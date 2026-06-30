<?php

namespace App\Http\Requests;

use App\Models\OvertimeRequest;
use Illuminate\Foundation\Http\FormRequest;

abstract class OvertimeRequestBaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function baseRules(bool $employeeRequired): array
    {
        return [
            'employee_id' => [$employeeRequired ? 'required' : 'nullable', 'exists:employees,id'],
            'work_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'total_hours' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Vui lòng chọn nhân viên.',
            'employee_id.exists' => 'Nhân viên không hợp lệ.',
            'work_date.required' => 'Vui lòng chọn ngày tăng ca.',
            'work_date.after_or_equal' => 'Ngày tăng ca không được nhỏ hơn ngày hiện tại.',
            'start_time.required' => 'Vui lòng nhập giờ bắt đầu.',
            'end_time.required' => 'Vui lòng nhập giờ kết thúc.',
            'end_time.after' => 'Giờ kết thúc phải lớn hơn giờ bắt đầu.',
            'reason.required' => 'Vui lòng nhập lý do tăng ca.',
        ];
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

            $query = OvertimeRequest::query()
                ->overlappingTime($employeeId, $workDate, $start, $end, $this->ignoreOvertimeRequestId());

            if ($query->exists()) {
                $validator->errors()->add('start_time', 'Khoảng thời gian tăng ca bị trùng trong cùng ngày.');
            }
        });
    }
}
