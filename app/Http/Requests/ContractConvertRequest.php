<?php

namespace App\Http\Requests;

use App\Models\Contract;
use App\Models\ContractType;
use App\Rules\NoContractOverlap;
use App\Services\ContractTypeConversionService;
use App\Services\ContractTypeValidationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ContractConvertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('salary')) {
            $this->merge([
                'salary' => str_replace('.', '', (string) $this->input('salary')),
            ]);
        }

        if (is_array($this->input('allowances'))) {
            $normalized = [];
            foreach ($this->input('allowances') as $key => $value) {
                $normalized[$key] = str_replace('.', '', (string) $value);
            }
            $this->merge(['allowances' => $normalized]);
        }

        if (! $this->filled('effective_date')) {
            $this->merge(['effective_date' => now()->toDateString()]);
        }

        if (! $this->filled('start_date')) {
            $this->merge(['start_date' => $this->input('effective_date')]);
        }
    }

    public function rules(): array
    {
        return [
            'contract_code' => ['nullable', 'string', 'max:50'],
            'contract_type_id' => ['required', 'exists:contract_types,id'],
            'effective_date' => ['required', 'date'],
            'start_date' => ['required', 'date', 'same:effective_date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'salary' => ['required', 'numeric', 'min:1'],
            'allowances' => ['nullable', 'array'],
            'allowances.*' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'contract_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        /** @var Contract $contract */
        $contract = $this->route('contract');

        $validator->after(function ($v) use ($contract) {
            if (! $contract) {
                return;
            }

            $contract->loadMissing('contractType');

            if ($contract->status === Contract::STATUS_REPLACED) {
                $v->errors()->add('contract', 'Hợp đồng này đã được thay thế.');
                return;
            }

            if (! $contract->canBeExtended()) {
                $v->errors()->add('contract', 'Chỉ chuyển loại được hợp đồng đang còn hiệu lực hoặc sắp hết hạn.');
                return;
            }

            if (! $this->contract_type_id || ! $this->effective_date) {
                return;
            }

            $targetType = ContractType::find($this->contract_type_id);
            if (! $targetType) {
                return;
            }

            try {
                app(ContractTypeConversionService::class)->assertConversionAllowed($contract, $targetType);
            } catch (\Illuminate\Validation\ValidationException $exception) {
                foreach ($exception->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        $v->errors()->add($field, $message);
                    }
                }
                return;
            }

            try {
                $normalized = app(ContractTypeValidationService::class)->validateAndNormalize(
                    $targetType,
                    $this->start_date,
                    $this->end_date
                );
                $this->merge(['end_date' => $normalized['end_date']]);
            } catch (\Illuminate\Validation\ValidationException $exception) {
                foreach ($exception->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        $v->errors()->add($field, $message);
                    }
                }
                return;
            }

            $rule = new NoContractOverlap(
                $contract->employee_id,
                $this->start_date,
                $this->end_date,
                $contract->id,
            );
            $rule->validate('start_date', null, function (string $message) use ($v) {
                $v->errors()->add('start_date', $message);
            });
        });
    }

    public function messages(): array
    {
        return [
            'start_date.same' => 'Ngày bắt đầu HĐ mới phải trùng ngày hiệu lực chuyển đổi.',
        ];
    }
}
