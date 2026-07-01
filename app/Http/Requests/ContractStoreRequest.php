<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Rules\NoContractOverlap;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContractStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'end_date' => ['required', 'date', 'after:start_date'],
            'salary' => ['required', 'numeric', 'min:1'],
            'allowance' => ['nullable', 'numeric', 'min:0'],
            'signed_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'contract_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'created_by' => ['nullable', 'exists:users,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if (! $this->employee_id || ! $this->start_date || ! $this->end_date) {
                return;
            }

            $employee = Employee::find($this->employee_id);
            if (! $employee || $employee->status !== 'active') {
                $v->errors()->add('employee_id', 'Không được tạo hợp đồng cho nhân viên đã nghỉ việc.');
                return;
            }

            $rule = new NoContractOverlap($this->employee_id, $this->start_date, $this->end_date);
            $rule->validate('start_date', null, function (string $message) use ($v) {
                $v->errors()->add('start_date', $message);
            });
        });
    }
}
