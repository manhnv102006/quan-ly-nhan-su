<?php

namespace App\Http\Requests;

use App\Models\Contract;
use App\Rules\NoContractOverlap;
use Illuminate\Foundation\Http\FormRequest;

class ContractExtendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Contract $contract */
        $contract = $this->route('contract');

        $startRule = $contract && $contract->end_date
            ? ['required', 'date', 'after:' . $contract->end_date->toDateString()]
            : ['required', 'date', 'after:' . $contract->start_date->toDateString()];

        return [
            'start_date' => $startRule,
            'end_date' => ['required', 'date', 'after:start_date'],
            'salary' => ['required', 'numeric', 'min:1'],
            'allowance' => ['nullable', 'numeric', 'min:0'],
            'contract_type_id' => ['required', 'exists:contract_types,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'contract_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ];
    }

    public function withValidator($validator): void
    {
        /** @var Contract $contract */
        $contract = $this->route('contract');

        $validator->after(function ($v) use ($contract) {
            if (! $contract || ! $this->start_date || ! $this->end_date) {
                return;
            }

            $rule = new NoContractOverlap($contract->employee_id, $this->start_date, $this->end_date);
            $rule->validate('start_date', null, function (string $message) use ($v) {
                $v->errors()->add('start_date', $message);
            });
        });
    }
}
