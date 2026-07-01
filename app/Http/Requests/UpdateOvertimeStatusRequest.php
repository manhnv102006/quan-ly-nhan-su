<?php

namespace App\Http\Requests;

use App\Models\OvertimeRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOvertimeStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                OvertimeRequest::STATUS_PENDING,
                OvertimeRequest::STATUS_APPROVED,
                OvertimeRequest::STATUS_REJECTED,
                OvertimeRequest::STATUS_COMPLETED,
            ])],
            'reject_reason' => [
                Rule::requiredIf(fn () => $this->input('status') === OvertimeRequest::STATUS_REJECTED),
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái.',
            'reject_reason.required' => 'Vui lòng nhập lý do từ chối.',
        ];
    }
}
