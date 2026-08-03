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

        $this->fillFromEmployeeProfile();
        $this->fillEndDateFromContractType();
    }

    /**
     * Phòng ban và chức vụ luôn bám theo hồ sơ nhân viên, không nhập lại trên form.
     */
    protected function fillFromEmployeeProfile(): void
    {
        $employee = $this->employee_id ? Employee::find($this->employee_id) : null;

        if (! $employee) {
            return;
        }

        $this->merge([
            'department_id' => $employee->department_id,
            'position_id' => $employee->position_id,
        ]);
    }

    /**
     * Bỏ trống ngày kết thúc thì suy ra từ thời hạn mặc định của loại hợp đồng.
     */
    protected function fillEndDateFromContractType(): void
    {
        if (filled($this->input('end_date')) || ! $this->contract_type_id || ! filled($this->input('start_date'))) {
            return;
        }

        $type = ContractType::find($this->contract_type_id);

        if (! $type) {
            return;
        }

        try {
            $endDate = app(ContractTypeValidationService::class)->suggestEndDate($type, (string) $this->input('start_date'));
        } catch (\Exception) {
            return; // Ngày bắt đầu không hợp lệ, để rule 'date' báo lỗi.
        }

        if ($endDate) {
            $this->merge(['end_date' => $endDate]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'department_id.required' => 'Nhân viên chưa có phòng ban trong hồ sơ. Vui lòng cập nhật hồ sơ nhân viên trước.',
            'position_id.required' => 'Nhân viên chưa có chức vụ trong hồ sơ. Vui lòng cập nhật hồ sơ nhân viên trước.',
        ];
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
            'signed_date' => ['required', 'date', 'before_or_equal:start_date'],
            'description' => ['required', 'string', 'max:1000'],
            'note' => ['required', 'string', 'max:1000'],
            'contract_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
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
