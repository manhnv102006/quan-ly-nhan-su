<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
            'type' => ['required', Rule::in(['system', 'leave', 'payroll', 'kpi'])],
            'audience' => ['required', Rule::in(['all', 'departments', 'selected'])],
            'send_mode' => ['required', Rule::in(['immediate', 'scheduled'])],
            'scheduled_at' => ['required_if:send_mode,scheduled', 'date', 'after:now'],
            'department_ids' => ['required_if:audience,departments', 'array', 'min:1'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
            'user_ids' => ['required_if:audience,selected', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề là bắt buộc.',
            'content.required' => 'Nội dung là bắt buộc.',
            'type.required' => 'Loại thông báo là bắt buộc.',
            'type.in' => 'Loại thông báo không hợp lệ.',
            'audience.required' => 'Đối tượng nhận là bắt buộc.',
            'send_mode.required' => 'Vui lòng chọn thời gian gửi.',
            'scheduled_at.required_if' => 'Vui lòng chọn ngày giờ gửi.',
            'scheduled_at.after' => 'Thời gian gửi phải ở tương lai.',
            'department_ids.required_if' => 'Vui lòng chọn ít nhất một phòng ban.',
            'department_ids.min' => 'Vui lòng chọn ít nhất một phòng ban.',
            'user_ids.required_if' => 'Vui lòng chọn ít nhất một người nhận.',
            'user_ids.min' => 'Vui lòng chọn ít nhất một người nhận.',
        ];
    }
}
