<?php

namespace App\Http\Requests;

use App\Models\ContractSuspension;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContractSuspendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::in(array_keys(ContractSuspension::REASON_LABELS))],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'expected_end_date' => ['required', 'date', 'after:start_date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Vui lòng chọn lý do tạm hoãn.',
            'start_date.after_or_equal' => 'Ngày bắt đầu tạm hoãn không được là ngày trong quá khứ.',
            'expected_end_date.after' => 'Ngày dự kiến tiếp tục phải sau ngày bắt đầu tạm hoãn.',
        ];
    }

    public function withValidator($validator): void
    {
        $contract = $this->route('contract');

        $validator->after(function ($v) use ($contract) {
            if (! $contract || ! $this->start_date) {
                return;
            }

            if ($contract->start_date && $this->start_date < $contract->start_date->toDateString()) {
                $v->errors()->add('start_date', 'Ngày tạm hoãn phải từ ngày bắt đầu hợp đồng trở đi.');
            }

            if ($contract->end_date && $this->start_date > $contract->end_date->toDateString()) {
                $v->errors()->add('start_date', 'Không tạm hoãn được sau ngày kết thúc hợp đồng.');
            }
        });
    }
}
