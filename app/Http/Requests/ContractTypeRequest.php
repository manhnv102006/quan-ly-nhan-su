<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContractTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $contractTypeId = $this->route('contract_type')?->id;

        return [
            'contract_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('contract_types', 'contract_name')->ignore($contractTypeId),
            ],
            'duration_month' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'contract_name.required' => 'Tên loại hợp đồng là bắt buộc.',
            'contract_name.unique' => 'Tên loại hợp đồng đã tồn tại.',
            'contract_name.max' => 'Tên loại hợp đồng không được vượt quá 100 ký tự.',
            'duration_month.required' => 'Thời hạn hợp đồng là bắt buộc.',
            'duration_month.integer' => 'Thời hạn hợp đồng phải là số nguyên.',
            'duration_month.min' => 'Thời hạn hợp đồng phải lớn hơn hoặc bằng 1 tháng.',
        ];
    }
}
