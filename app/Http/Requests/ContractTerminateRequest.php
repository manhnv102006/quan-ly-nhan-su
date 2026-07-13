<?php

namespace App\Http\Requests;

use App\Models\ContractTermination;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContractTerminateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::in(array_keys(ContractTermination::REASON_LABELS))],
            'end_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $contract = $this->route('contract');
        $validator->after(function ($v) use ($contract) {
            if (! $contract || ! $this->end_date) {
                return;
            }

            if ($contract->start_date && $this->end_date < $contract->start_date->toDateString()) {
                $v->errors()->add('end_date', 'Ngày chấm dứt phải sau hoặc bằng ngày bắt đầu.');
            }
        });
    }
}
