<?php

namespace App\Http\Requests;

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Employee;
use App\Rules\NoContractOverlap;
use App\Services\ContractTypeValidationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContractStoreRequest extends FormRequest
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
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'contract_type_id' => ['required', 'exists:contract_types,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'contract_code' => ['nullable', 'string', 'max:50', Rule::unique('contracts', 'contract_code')],
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
        $validator->after(function ($v) {
            if (! $this->employee_id || ! $this->start_date || ! $this->contract_type_id) {
                return;
            }

            $employee = Employee::find($this->employee_id);
            if (! $employee || $employee->status !== 'active') {
                $v->errors()->add('employee_id', 'Chỉ được tạo hợp đồng cho nhân viên đang hoạt động.');
                return;
            }

            $hasActiveContract = Contract::query()
                ->forEmployee($employee->id)
                ->where('status', Contract::STATUS_ACTIVE)
                ->exists();

            if ($hasActiveContract) {
                $v->errors()->add(
                    'employee_id',
                    'Nhân viên đã có hợp đồng hiệu lực, vui lòng gia hạn/chuyển loại thay vì tạo mới'
                );
                return;
            }

            $type = ContractType::find($this->contract_type_id);
            if (! $type) {
                return;
            }

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

            $rule = new NoContractOverlap($this->employee_id, $this->start_date, $this->end_date);
            $rule->validate('start_date', null, function (string $message) use ($v) {
                $v->errors()->add('start_date', $message);
            });
        });
    }
}
