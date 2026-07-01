<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'assignment_scope' => ['required', Rule::in(['employee', 'department', 'company'])],
            'shift_id' => ['required', 'exists:shifts,id'],
            'work_date' => ['required', 'date'],
            'employee_id' => [
                Rule::excludeIf(fn () => $this->input('assignment_scope') !== 'employee'),
                'required',
                'exists:employees,id',
            ],
            'department_id' => [
                Rule::excludeIf(fn () => $this->input('assignment_scope') !== 'department'),
                'required',
                'exists:departments,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'assignment_scope.required' => 'Vui lòng chọn phạm vi gán ca.',
            'assignment_scope.in' => 'Phạm vi gán ca không hợp lệ.',
            'shift_id.required' => 'Vui lòng chọn ca làm.',
            'shift_id.exists' => 'Ca làm không tồn tại.',
            'work_date.required' => 'Vui lòng chọn ngày làm.',
            'work_date.date' => 'Ngày làm không hợp lệ.',
            'employee_id.required' => 'Vui lòng chọn nhân viên.',
            'employee_id.exists' => 'Nhân viên không tồn tại.',
            'department_id.required' => 'Vui lòng chọn phòng ban.',
            'department_id.exists' => 'Phòng ban không tồn tại.',
        ];
    }
}
