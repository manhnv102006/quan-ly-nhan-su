<?php

namespace App\Http\Requests;

use App\Models\OvertimeRequest;
use App\Services\OvertimeLeaveConflictService;
use App\Services\OvertimeLimitService;
use App\Services\OvertimeProhibitionService;
use App\Support\OvertimeReasonRules;
use App\Support\TimeInput;
use Illuminate\Foundation\Http\FormRequest;

abstract class OvertimeRequestBaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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

    protected function baseRules(bool $employeeRequired): array
    {
        return [
            'employee_id' => [$employeeRequired ? 'required' : 'nullable', 'exists:employees,id'],
            'work_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'total_hours' => ['nullable', 'numeric', 'min:0'],
            'rate_multiplier' => ['required', 'numeric', 'in:1.5,2.0,3.0'],
            'reason' => OvertimeReasonRules::rules(OvertimeReasonRules::maxLengthForAdmin()),
        ];
    }

    public function messages(): array
    {
        return array_merge(OvertimeReasonRules::messages(), [
            'employee_id.required' => 'Vui lòng chọn nhân viên.',
            'employee_id.exists' => 'Nhân viên không hợp lệ.',
            'work_date.required' => 'Vui lòng chọn ngày tăng ca.',
            'work_date.after_or_equal' => 'Ngày tăng ca không được nhỏ hơn ngày hiện tại.',
            'start_time.required' => 'Vui lòng nhập giờ bắt đầu.',
            'start_time.date_format' => 'Giờ bắt đầu phải đúng định dạng HH:MM (24 giờ).',
            'end_time.required' => 'Vui lòng nhập giờ kết thúc.',
            'end_time.date_format' => 'Giờ kết thúc phải đúng định dạng HH:MM (24 giờ).',
            'end_time.after' => 'Giờ kết thúc phải lớn hơn giờ bắt đầu.',
            'rate_multiplier.required' => 'Vui lòng chọn loại ngày tăng ca.',
            'rate_multiplier.in' => 'Loại ngày tăng ca không hợp lệ.',
        ]);
    }

    protected function ignoreOvertimeRequestId(): ?int
    {
        return null;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $employeeId = $this->input('employee_id') ?? $this->user()?->employee?->id;
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

            $query = OvertimeRequest::query()
                ->overlappingActiveTime($employeeId, $workDate, $start, $end, $this->ignoreOvertimeRequestId());

            if ($query->exists()) {
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

            $violations = $limitService->violations(
                (int) $employeeId,
                (string) $workDate,
                $newHours,
                $this->ignoreOvertimeRequestId(),
            );

            foreach ($violations as $field => $message) {
                $validator->errors()->add($field, $message);
            }
        });
    }
}
