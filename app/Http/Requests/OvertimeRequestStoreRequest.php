<?php

namespace App\Http\Requests;

use App\Models\OvertimeRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OvertimeRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'exists:employees,id'],
            'work_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'total_hours' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['required', 'string'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected', 'completed'])],
            'approved_by' => ['nullable', 'exists:users,id'],
            'approved_at' => ['nullable', 'date'],
            'reject_reason' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'work_date.required' => 'Vui lòng chọn ngày tăng ca.',
            'work_date.after_or_equal' => 'Ngày tăng ca không được nhỏ hơn ngày hiện tại.',
            'start_time.required' => 'Vui lòng nhập giờ bắt đầu.',
            'end_time.required' => 'Vui lòng nhập giờ kết thúc.',
            'end_time.after' => 'Giờ kết thúc phải lớn hơn giờ bắt đầu.',
            'reason.required' => 'Vui lòng nhập lý do tăng ca.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $employeeId = $this->input('employee_id') ?? $this->user()?->employee?->id;

            if (! $employeeId || ! $this->work_date || ! $this->start_time || ! $this->end_time) {
                return;
            }

            $isOverlapped = OvertimeRequest::query()
                ->where('employee_id', $employeeId)
                ->whereDate('work_date', $this->work_date)
                ->where('start_time', '<', $this->end_time)
                ->where('end_time', '>', $this->start_time)
                ->exists();

            if ($isOverlapped) {
                $validator->errors()->add('start_time', 'Khoảng thời gian tăng ca bị trùng trong cùng ngày.');
            }
        });
    }
}
