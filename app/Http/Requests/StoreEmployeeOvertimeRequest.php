<?php

namespace App\Http\Requests;

use App\Models\OvertimeRequest;
use App\Support\OvertimeReasonRules;
use App\Support\TimeInput;
use App\Services\OvertimeLeaveConflictService;
use App\Services\OvertimeLimitService;
use App\Services\OvertimeProhibitionService;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeOvertimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->isEmployee() || $user?->isManager() || false;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('start_time')) {
            $merge['start_time'] = TimeInput::forInput($this->input('start_time'));
        }

        if ($this->has('end_time')) {
            $merge['end_time'] = TimeInput::forInput($this->input('end_time'));
        }

        if ($this->has('reason')) {
            $merge['reason'] = trim((string) $this->input('reason'));
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'work_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'rate_multiplier' => ['required', 'numeric', 'in:1.5,2.0,3.0'],
            'reason' => OvertimeReasonRules::rules(OvertimeReasonRules::maxLengthForEmployee()),
            'voluntary_consent' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return array_merge(OvertimeReasonRules::messages(), [
            'work_date.required' => 'Vui lòng chọn ngày tăng ca.',
            'work_date.after_or_equal' => 'Ngày tăng ca phải từ hôm nay trở đi, không được chọn ngày trong quá khứ.',
            'start_time.required' => 'Vui lòng nhập giờ bắt đầu.',
            'end_time.required' => 'Vui lòng nhập giờ kết thúc.',
            'end_time.after' => 'Giờ kết thúc phải lớn hơn giờ bắt đầu.',
            'rate_multiplier.required' => 'Vui lòng chọn loại ngày tăng ca.',
            'rate_multiplier.in' => 'Loại ngày tăng ca không hợp lệ.',
            'voluntary_consent.accepted' => 'Bạn phải xác nhận đồng ý làm thêm giờ tự nguyện trước khi gửi đơn.',
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $employeeId = $this->user()?->employee?->id;
            $start = $this->input('start_time');
            $end = $this->input('end_time');
            $workDate = $this->input('work_date');

            if (! $employeeId || ! $workDate || ! $start || ! $end) {
                return;
            }

            $prohibition = app(OvertimeProhibitionService::class)->violationMessage((int) $employeeId);

            if ($prohibition !== null) {
                $validator->errors()->add('work_date', $prohibition);

                return;
            }

            $exists = OvertimeRequest::query()
                ->overlappingActiveTime($employeeId, $workDate, $start, $end)
                ->exists();

            if ($exists) {
                $validator->errors()->add('start_time', 'Khoảng thời gian tăng ca bị trùng với đơn khác trong cùng ngày.');

                return;
            }

            $leaveConflict = app(OvertimeLeaveConflictService::class)->violationMessage(
                (int) $employeeId,
                (string) $workDate,
                (string) $start,
                (string) $end,
            );

            if ($leaveConflict !== null) {
                $validator->errors()->add('work_date', $leaveConflict);

                return;
            }

            $limitService = app(OvertimeLimitService::class);
            $newHours = $limitService->hoursBetween((string) $start, (string) $end);

            foreach ($limitService->violations((int) $employeeId, (string) $workDate, $newHours) as $field => $message) {
                $validator->errors()->add($field, $message);
            }
        });
    }
}
