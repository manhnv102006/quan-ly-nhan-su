<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $allowance = $this->input('allowance');

        $this->merge([
            'position_name' => trim((string) $this->input('position_name', '')),
            'description' => $this->filled('description')
                ? trim((string) $this->input('description'))
                : null,
            'allowance' => ($allowance === null || $allowance === '') ? 0 : $allowance,
        ]);
    }

    public function rules(): array
    {
        return [
            'position_name' => [
                'required',
                'string',
                'min:2',
                'max:30',
                'regex:/^[\p{L}\s]+$/u',
                Rule::unique('positions', 'position_name'),
            ],
            'base_salary' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'allowance' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'position_name.required' => 'Vui lòng nhập tên chức vụ.',
            'position_name.min' => 'Tên chức vụ phải có ít nhất 2 ký tự.',
            'position_name.max' => 'Tên chức vụ không được vượt quá 30 ký tự.',
            'position_name.regex' => 'Tên chức vụ chỉ được chứa chữ cái, không được nhập số hoặc ký tự đặc biệt.',
            'position_name.unique' => 'Tên chức vụ đã tồn tại trong hệ thống.',

            'base_salary.required' => 'Vui lòng nhập lương cơ bản.',
            'base_salary.numeric' => 'Lương cơ bản phải là số hợp lệ.',
            'base_salary.min' => 'Lương cơ bản không được âm.',
            'base_salary.max' => 'Lương cơ bản vượt quá giới hạn cho phép.',

            'allowance.numeric' => 'Phụ cấp chức vụ phải là số hợp lệ.',
            'allowance.min' => 'Phụ cấp chức vụ không được âm.',
            'allowance.max' => 'Phụ cấp chức vụ vượt quá giới hạn cho phép.',

            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];
    }

    public function attributes(): array
    {
        return [
            'position_name' => 'tên chức vụ',
            'base_salary' => 'lương cơ bản',
            'allowance' => 'phụ cấp chức vụ',
            'description' => 'mô tả',
            'status' => 'trạng thái',
        ];
    }
}
