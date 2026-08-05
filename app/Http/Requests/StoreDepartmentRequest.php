<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'department_code' => strtoupper(trim((string) $this->input('department_code', ''))),
            'department_name' => trim((string) $this->input('department_name', '')),
            'description' => $this->filled('description')
                ? trim((string) $this->input('description'))
                : null,
        ]);
    }

    public function rules(): array
    {
        $minMaxEmployees = Department::MIN_MAX_EMPLOYEES;
        $maxMaxEmployees = Department::MAX_MAX_EMPLOYEES;
        $minMaxManagers = Department::MIN_MAX_MANAGERS;
        $maxMaxManagers = Department::MAX_MAX_MANAGERS;

        return [
            'department_code' => [
                'required',
                'string',
                'min:2',
                'max:20',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('departments', 'department_code'),
            ],
            'department_name' => ['required', 'string', 'min:2', 'max:100'],
            'description' => ['nullable', 'string'],
            'max_employees' => ['required', 'integer', "min:{$minMaxEmployees}", "max:{$maxMaxEmployees}"],
            'max_managers' => ['required', 'integer', "min:{$minMaxManagers}", "max:{$maxMaxManagers}"],
            'manager_id' => ['nullable', 'integer', 'exists:employees,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'department_code.required' => 'Vui lòng nhập mã phòng ban.',
            'department_code.min' => 'Mã phòng ban phải có ít nhất 2 ký tự.',
            'department_code.max' => 'Mã phòng ban không được vượt quá 20 ký tự.',
            'department_code.regex' => 'Mã phòng ban chỉ được chứa chữ in hoa, số, gạch ngang và gạch dưới.',
            'department_code.unique' => 'Mã phòng ban đã tồn tại trong hệ thống.',
            'department_name.required' => 'Vui lòng nhập tên phòng ban.',
            'department_name.min' => 'Tên phòng ban phải có ít nhất 2 ký tự.',
            'department_name.max' => 'Tên phòng ban không được vượt quá 100 ký tự.',
            'max_employees.required' => 'Vui lòng nhập giới hạn nhân viên.',
            'max_employees.integer' => 'Giới hạn nhân viên phải là số nguyên.',
            'max_employees.min' => 'Giới hạn nhân viên tối thiểu là '.Department::MIN_MAX_EMPLOYEES.'.',
            'max_employees.max' => 'Giới hạn nhân viên tối đa là '.Department::MAX_MAX_EMPLOYEES.'.',
            'max_managers.required' => 'Vui lòng nhập giới hạn quản lý.',
            'max_managers.integer' => 'Giới hạn quản lý phải là số nguyên.',
            'max_managers.min' => 'Giới hạn quản lý tối thiểu là '.Department::MIN_MAX_MANAGERS.'.',
            'max_managers.max' => 'Giới hạn quản lý tối đa là '.Department::MAX_MAX_MANAGERS.'.',
            'manager_id.integer' => 'Quản lý phòng ban không hợp lệ.',
            'manager_id.exists' => 'Quản lý được chọn không tồn tại.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];
    }
}
