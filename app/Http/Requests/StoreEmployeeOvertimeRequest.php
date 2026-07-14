<?php

namespace App\Http\Requests;

use App\Models\OvertimeRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeOvertimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->isEmployee() || $user?->isManager() || false;
    }

    public function rules(): array
    {
        return [
            'work_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'rate_multiplier' => ['required', 'numeric', 'in:1.5,2.0,3.0'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'work_date.required' => 'Vui lòng chọn ngày tăng ca.',
            'start_time.required' => 'Vui lòng nhập giờ bắt đầu.',
            'end_time.required' => 'Vui lòng nhập giờ kết thúc.',
            'end_time.after' => 'Giờ kết thúc phải lớn hơn giờ bắt đầu.',
            'rate_multiplier.required' => 'Vui lòng chọn loại ngày tăng ca.',
            'rate_multiplier.in' => 'Loại ngày tăng ca không hợp lệ.',
            'reason.required' => 'Vui lòng nhập lý do tăng ca.',
        ];
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

            $exists = OvertimeRequest::query()
                ->overlappingTime($employeeId, $workDate, $start, $end)
                ->whereIn('status', [
                    OvertimeRequest::STATUS_PENDING,
                    OvertimeRequest::STATUS_APPROVED,
                    OvertimeRequest::STATUS_COMPLETED,
                ])
                ->exists();

            if ($exists) {
                $validator->errors()->add('start_time', 'Khoảng thời gian tăng ca bị trùng với đơn khác trong cùng ngày.');
            }
        });
    }
}
