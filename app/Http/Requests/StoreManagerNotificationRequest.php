<?php

namespace App\Http\Requests;

use App\Support\ManagerDepartmentResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManagerNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->isManager()
            && ManagerDepartmentResolver::managedDepartmentId($user) !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
            'audience' => ['required', Rule::in(['all', 'selected'])],
            'user_ids' => ['required_if:audience,selected', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề là bắt buộc.',
            'content.required' => 'Nội dung là bắt buộc.',
            'audience.required' => 'Đối tượng nhận là bắt buộc.',
            'user_ids.required_if' => 'Vui lòng chọn ít nhất một thành viên.',
            'user_ids.min' => 'Vui lòng chọn ít nhất một thành viên.',
        ];
    }
}
