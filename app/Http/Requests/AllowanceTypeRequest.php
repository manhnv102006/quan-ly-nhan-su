<?php

namespace App\Http\Requests;

use App\Models\AllowanceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AllowanceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('default_amount')) {
            $this->merge([
                'default_amount' => str_replace('.', '', (string) $this->input('default_amount')),
            ]);
        }
    }

    public function rules(): array
    {
        $type = $this->route('allowance_type');
        $typeId = $type?->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('allowance_types', 'code')->ignore($typeId),
            ],
            'default_amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
            'calculation_note' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }
}
