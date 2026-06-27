<?php

namespace App\Http\Requests;

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
}
