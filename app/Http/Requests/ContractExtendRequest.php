<?php

namespace App\Http\Requests;

use App\Models\Contract;
use App\Models\ContractType;
use App\Rules\NoContractOverlap;
use App\Services\ContractTypeValidationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ContractExtendRequest extends FormRequest
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
    }

    public function rules(): array
    {
        /** @var Contract $contract */
        $contract = $this->route('contract');

        $minStart = $contract?->end_date
            ? $contract->end_date->copy()->addDay()->toDateString()
            : ($contract?->start_date?->toDateString() ?? now()->toDateString());

        return [
            'contract_code' => ['nullable', 'string', 'max:50'],
            'start_date' => ['required', 'date', 'after_or_equal:'.$minStart],
            'end_date' => ['required', 'date', 'after:start_date'],
            'salary' => ['required', 'numeric', 'min:1'],
            'allowances' => ['nullable', 'array'],
            'allowances.*' => ['nullable', 'numeric', 'min:0'],
            'contract_type_id' => ['required', 'exists:contract_types,id'],
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
                $v->errors()->add('contract', 'Hợp đồng này đã được thay thế, không thể gia hạn.');
                return;
            }

            if (! $contract->canBeExtended()) {
                $v->errors()->add('contract', 'Chỉ gia hạn được hợp đồng đang còn hiệu lực hoặc sắp hết hạn.');
                return;
            }

            if ($contract->isFixedTermRenewalBlocked()) {
                $v->errors()->add(
                    'contract',
                    Contract::fixedTermRenewalBlockedMessage()
                );
                return;
            }

            if (! $this->start_date || ! $this->contract_type_id) {
                return;
            }

            $type = ContractType::find($this->contract_type_id);
            if ($type) {
                try {
                    app(ContractTypeValidationService::class)->validateAndNormalize(
                        $type,
                        $this->start_date,
                        $this->end_date
                    );
                } catch (\Illuminate\Validation\ValidationException $exception) {
                    foreach ($exception->errors() as $field => $messages) {
                        foreach ($messages as $message) {
                            $v->errors()->add($field, $message);
                        }
                    }
                    return;
                }
            }

            if (! $this->end_date) {
                return;
            }

            $rule = new NoContractOverlap($contract->employee_id, $this->start_date, $this->end_date, $contract->id);
            $rule->validate('start_date', null, function (string $message) use ($v) {
                $v->errors()->add('start_date', $message);
            });
        });
    }

    public function messages(): array
    {
        return [
            'start_date.after_or_equal' => 'Ngày bắt đầu HĐ mới phải từ ngày kế tiếp sau ngày kết thúc HĐ hiện tại.',
        ];
    }
}
