<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resume_date' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'resume_date.required' => 'Vui lòng chọn ngày tiếp tục hợp đồng.',
            'resume_date.before_or_equal' => 'Ngày tiếp tục không được ở tương lai.',
        ];
    }
}
