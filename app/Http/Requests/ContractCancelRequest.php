<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractCancelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'end_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
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
                $v->errors()->add('end_date', 'Ngày hủy phải sau hoặc bằng ngày bắt đầu.');
            }
        });
    }
}
