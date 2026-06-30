<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// cập nhật tiến độ của nhân viên
class UpdateEmployeeKPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Lấy bản ghi EmployeeKpi từ route
        $employeeKpi = $this->route('employeeKpi');

        // Sử dụng policy để kiểm tra quyền cập nhật
        return $this->user()->can('update', $employeeKpi);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'progress' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'string', Rule::in(['pending', 'in_progress', 'completed'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'progress.required' => 'Vui lòng nhập tiến độ.',
            'progress.numeric' => 'Tiến độ phải là một con số.',
            'progress.min' => 'Tiến độ không được nhỏ hơn 0.',
            'progress.max' => 'Tiến độ không được lớn hơn 100.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];
    }
}
