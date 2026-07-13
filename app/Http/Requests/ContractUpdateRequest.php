<?php

namespace App\Http\Requests;

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Employee;
use App\Rules\NoContractOverlap;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContractUpdateRequest extends FormRequest
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
        $contractId = $contract?->id;

        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'contract_type_id' => ['required', 'exists:contract_types,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'contract_code' => ['nullable', 'string', 'max:50', Rule::unique('contracts', 'contract_code')->ignore($contractId)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'salary' => ['required', 'numeric', 'min:1'],
            'allowances' => ['nullable', 'array'],
            'allowances.*' => ['nullable', 'numeric', 'min:0'],
            'signed_date' => ['nullable', 'date', 'before_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'contract_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ];
    }

    public function withValidator($validator): void
    {
        $contract = $this->route('contract');

        $validator->after(function ($v) use ($contract) {
            if (! $this->employee_id || ! $this->start_date || ! $this->end_date) {
                return;
            }

            $employee = Employee::find($this->employee_id);
            if (! $employee || $employee->status !== 'active') {
                $v->errors()->add('employee_id', 'Không được tạo hợp đồng cho nhân viên đã nghỉ việc.');
                return;
            }

            $ignoreId = $contract?->id;
            $rule = new NoContractOverlap($this->employee_id, $this->start_date, $this->end_date, $ignoreId);
            $rule->validate('start_date', null, function (string $message) use ($v) {
                $v->errors()->add('start_date', $message);
            });

            if ($this->contract_type_id) {
                $type = ContractType::find($this->contract_type_id);
                if ($type?->requiresEndDate() && empty($this->end_date)) {
                    $v->errors()->add('end_date', 'Loại hợp đồng này yêu cầu ngày kết thúc.');
                }
            }
        });
    }
}
