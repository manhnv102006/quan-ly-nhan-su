<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class ApprovalRejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reject_reason' => trim((string) $this->input('reject_reason', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'reject_reason' => ['required', 'string', 'min:1', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reject_reason.required' => 'Vui lòng nhập lý do từ chối.',
            'reject_reason.min' => 'Vui lòng nhập lý do từ chối.',
            'reject_reason.max' => 'Lý do từ chối không được vượt quá 1000 ký tự.',
        ];
    }
}
