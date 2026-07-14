<?php

namespace App\Http\Requests;

use App\Rules\DepartmentEmployeeCapacity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'employee_code' => strtoupper(trim((string) $this->input('employee_code', ''))),
            'full_name' => trim((string) $this->input('full_name', '')),
            'email' => strtolower(trim((string) $this->input('email', ''))),
            'phone' => preg_replace('/\s+/', '', (string) $this->input('phone', '')),
            'address' => $this->filled('address') ? trim((string) $this->input('address')) : null,
        ]);
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id;

        return array_merge([
            'employee_code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('employees', 'employee_code')->ignore($employeeId),
            ],
            'full_name' => ['required', 'string', 'min:2', 'max:100'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['required', 'date', 'before:today', 'before_or_equal:'.now()->subYears(16)->toDateString()],
            'phone' => ['required', 'string', 'regex:/^0[0-9]{9,10}$/'],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('employees', 'email')->ignore($employeeId),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'department_id' => [
                'required',
                'exists:departments,id',
                new DepartmentEmployeeCapacity($employeeId),
            ],
            'position_id' => ['required', 'exists:positions,id'],
            'hire_date' => ['required', 'date', 'after_or_equal:date_of_birth'],
            'status' => ['required', Rule::in(['active', 'inactive', 'resigned'])],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('employees', 'user_id')->ignore($employeeId),
            ],
        ], $this->documentRules());
    }

    /**
     * @return array<string, mixed>
     */
    protected function documentRules(): array
    {
        return [
            'documents' => ['nullable', 'array'],
            'documents.*.document_name' => ['nullable', 'string', 'max:255'],
            'documents.*.document_type' => ['nullable', Rule::in(['cccd', 'cv', 'certificate', 'degree', 'contract'])],
            'documents.*.file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'remove_documents' => ['nullable', 'array'],
            'remove_documents.*' => ['integer', 'exists:employee_documents,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_code.required' => 'Vui lòng nhập mã nhân viên.',
            'employee_code.max' => 'Mã nhân viên không được vượt quá 20 ký tự.',
            'employee_code.regex' => 'Mã nhân viên chỉ được chứa chữ cái, số, dấu gạch ngang hoặc gạch dưới.',
            'employee_code.unique' => 'Mã nhân viên đã tồn tại trong hệ thống.',

            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'full_name.max' => 'Họ và tên không được vượt quá 100 ký tự.',

            'gender.required' => 'Vui lòng chọn giới tính.',
            'gender.in' => 'Giới tính không hợp lệ.',

            'date_of_birth.required' => 'Vui lòng chọn ngày sinh.',
            'date_of_birth.date' => 'Ngày sinh không hợp lệ.',
            'date_of_birth.before' => 'Ngày sinh phải trước ngày hiện tại.',
            'date_of_birth.before_or_equal' => 'Nhân viên phải đủ ít nhất 16 tuổi.',

            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại phải bắt đầu bằng 0 và có 10–11 chữ số.',

            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 100 ký tự.',
            'email.unique' => 'Email đã được sử dụng bởi nhân viên khác.',

            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',

            'department_id.required' => 'Vui lòng chọn phòng ban.',
            'department_id.exists' => 'Phòng ban không hợp lệ.',

            'position_id.required' => 'Vui lòng chọn chức vụ.',
            'position_id.exists' => 'Chức vụ không hợp lệ.',

            'hire_date.required' => 'Vui lòng chọn ngày vào làm.',
            'hire_date.date' => 'Ngày vào làm không hợp lệ.',
            'hire_date.after_or_equal' => 'Ngày vào làm phải sau hoặc bằng ngày sinh.',

            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',

            'user_id.exists' => 'Tài khoản liên kết không tồn tại.',
            'user_id.unique' => 'Tài khoản này đã được liên kết với nhân viên khác.',

            'documents.*.document_name.max' => 'Tên tài liệu không được vượt quá 255 ký tự.',
            'documents.*.document_type.in' => 'Loại tài liệu không hợp lệ.',
            'documents.*.file.file' => 'File tài liệu không hợp lệ.',
            'documents.*.file.max' => 'Mỗi file tài liệu không được vượt quá 10MB.',
            'documents.*.file.mimes' => 'File tài liệu phải là PDF, Word hoặc hình ảnh (JPG, PNG).',
        ];
    }

    public function attributes(): array
    {
        return [
            'employee_code' => 'mã nhân viên',
            'full_name' => 'họ và tên',
            'gender' => 'giới tính',
            'date_of_birth' => 'ngày sinh',
            'phone' => 'số điện thoại',
            'email' => 'email',
            'address' => 'địa chỉ',
            'department_id' => 'phòng ban',
            'position_id' => 'chức vụ',
            'hire_date' => 'ngày vào làm',
            'status' => 'trạng thái',
            'user_id' => 'tài khoản liên kết',
        ];
    }
}
