<?php

namespace App\Http\Requests;

use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Các ô bị ẩn trong form vẫn gửi giá trị cũ lên, nên dọn sạch những trường
     * không còn ý nghĩa với lựa chọn hiện tại trước khi validate.
     */
    protected function prepareForValidation(): void
    {
        $leaveType = $this->route('leave_type');
        $quotaType = $this->input('quota_type');
        $payer = $this->input('salary_payer');
        $requiresDocument = $this->boolean('requires_document');
        $numericQuota = in_array($quotaType, LeaveType::NUMERIC_QUOTA_TYPES, true);
        $payerHasPercent = in_array($payer, [LeaveType::PAYER_COMPANY, LeaveType::PAYER_INSURANCE], true);

        $this->merge([
            'code' => $leaveType?->is_system
                ? $leaveType->code
                : strtolower(trim((string) $this->input('code'))),
            'enforce_quota' => $this->boolean('enforce_quota')
                && in_array($quotaType, LeaveType::ENFORCEABLE_QUOTA_TYPES, true),
            'requires_document' => $requiresDocument,
            'counts_as_leave' => $this->boolean('counts_as_leave'),
            'auto_generated' => $this->boolean('auto_generated'),
            'is_active' => $this->boolean('is_active'),
            'gender_restriction' => $this->input('gender_restriction') ?: null,
            'document_hint' => $requiresDocument ? $this->input('document_hint') : null,
            'quota_days' => $numericQuota ? $this->nullIfBlank('quota_days') : null,
            'salary_percent' => $payerHasPercent ? $this->nullIfBlank('salary_percent') : null,
            'sort_order' => $this->nullIfBlank('sort_order') ?? 0,
        ]);
    }

    private function nullIfBlank(string $key): mixed
    {
        $value = $this->input($key);

        return $value === null || $value === '' ? null : $value;
    }

    public function rules(): array
    {
        $leaveType = $this->route('leave_type');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('leave_types', 'code')->ignore($leaveType?->getKey()),
            ],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'annual_deduction' => ['required', Rule::in(array_keys(LeaveType::ANNUAL_DEDUCTION_LABELS))],
            'salary_payer' => ['required', Rule::in(array_keys(LeaveType::PAYER_LABELS))],
            'salary_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'quota_type' => ['required', Rule::in(array_keys(LeaveType::QUOTA_LABELS))],
            'quota_days' => [
                Rule::requiredIf(fn () => in_array($this->input('quota_type'), LeaveType::NUMERIC_QUOTA_TYPES, true)),
                'nullable',
                'numeric',
                'min:0.5',
                'max:999',
            ],
            'quota_note' => ['nullable', 'string', 'max:255'],
            'enforce_quota' => ['boolean'],
            'requires_document' => ['boolean'],
            'document_hint' => [
                Rule::requiredIf(fn () => $this->boolean('requires_document')),
                'nullable',
                'string',
                'max:255',
            ],
            'gender_restriction' => ['nullable', Rule::in(array_keys(LeaveType::GENDER_LABELS))],
            'counts_as_leave' => ['boolean'],
            'auto_generated' => ['boolean'],
            'color' => ['required', Rule::in(array_keys(LeaveType::COLOR_CLASSES))],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Vui lòng nhập mã loại nghỉ phép.',
            'code.regex' => 'Mã chỉ gồm chữ thường, số và dấu gạch dưới, bắt đầu bằng chữ cái. Ví dụ: nghi_viec_rieng.',
            'code.unique' => 'Mã loại nghỉ phép này đã tồn tại.',
            'name.required' => 'Vui lòng nhập tên loại nghỉ phép.',
            'annual_deduction.required' => 'Vui lòng chọn cách trừ phép năm.',
            'annual_deduction.in' => 'Cách trừ phép năm không hợp lệ.',
            'salary_payer.required' => 'Vui lòng chọn bên chi trả lương.',
            'salary_payer.in' => 'Bên chi trả lương không hợp lệ.',
            'salary_percent.numeric' => 'Tỷ lệ hưởng lương phải là số.',
            'salary_percent.max' => 'Tỷ lệ hưởng lương tối đa 100%.',
            'quota_type.required' => 'Vui lòng chọn cách áp dụng hạn mức.',
            'quota_type.in' => 'Cách áp dụng hạn mức không hợp lệ.',
            'quota_days.required' => 'Vui lòng nhập số ngày/lần/tháng cho hạn mức đã chọn.',
            'quota_days.numeric' => 'Hạn mức phải là số.',
            'quota_days.min' => 'Hạn mức tối thiểu 0,5.',
            'document_hint.required' => 'Vui lòng mô tả giấy tờ cần nộp khi bắt buộc đính kèm.',
            'gender_restriction.in' => 'Giới hạn giới tính không hợp lệ.',
            'color.required' => 'Vui lòng chọn màu huy hiệu.',
            'color.in' => 'Màu huy hiệu không hợp lệ.',
        ];
    }
}
