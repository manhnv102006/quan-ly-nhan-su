<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ContractType;

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
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('contract_types', 'code')->ignore($contractTypeId),
            ],
            'category' => ['required', Rule::in(array_keys(ContractType::CATEGORY_LABELS))],
            'duration_month' => ['required', 'integer', 'min:0', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
