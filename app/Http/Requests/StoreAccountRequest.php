<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => trim((string) $this->input('username', '')),
            'name' => trim((string) $this->input('name', '')),
            'email' => strtolower(trim((string) $this->input('email', ''))),
        ]);
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/',
                'unique:users,username',
            ],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['required', 'confirmed', Password::min(8)->max(255)],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.min' => 'Tên đăng nhập phải có ít nhất 3 ký tự.',
            'username.max' => 'Tên đăng nhập không được vượt quá 50 ký tự.',
            'username.regex' => 'Tên đăng nhập chỉ được chứa chữ cái, số, dấu gạch ngang và gạch dưới.',
            'username.unique' => 'Tên đăng nhập đã tồn tại trong hệ thống.',
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'email.unique' => 'Email đã được sử dụng bởi tài khoản khác.',
            'role_id.required' => 'Vui lòng chọn vai trò.',
            'role_id.integer' => 'Vai trò không hợp lệ.',
            'role_id.exists' => 'Vai trò không tồn tại.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.max' => 'Mật khẩu không được vượt quá 255 ký tự.',
        ];
    }
}
