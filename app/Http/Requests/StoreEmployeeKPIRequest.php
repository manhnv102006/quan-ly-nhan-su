<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Models\EmployeeKPI;
use App\Models\KPI;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeKPIRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kpi_id' => ['required', 'exists:kpis,id'],
            'employee_id' => [
                'required',
                'exists:employees,id',
                // Nhân viên phải thuộc đúng phòng ban + chức vụ của mẫu KPI và chưa được giao KPI này
                function ($attribute, $value, $fail) {
                    $kpi = KPI::with('departments')->find($this->input('kpi_id'));
                    $employee = Employee::with('user.role')->find($value);

                    if (! $kpi || ! $employee) {
                        return;
                    }

                    $departmentIds = $kpi->departments->pluck('id')->all();
                    if ($departmentIds && ! in_array((int) $employee->department_id, array_map('intval', $departmentIds), true)) {
                        $fail('Nhân viên không thuộc phòng ban áp dụng của KPI này.');
                        return;
                    }

                    $positions = $kpi->positions ?? [];
                    if ($positions) {
                        $roleName = $employee->user?->role?->name;
                        if (! $roleName || ! in_array($roleName, $positions, true)) {
                            $fail('Nhân viên không thuộc chức vụ áp dụng của KPI này.');
                            return;
                        }
                    }

                    $alreadyAssigned = EmployeeKPI::query()
                        ->where('kpi_id', $kpi->id)
                        ->where('employee_id', $employee->id)
                        ->exists();

                    if ($alreadyAssigned) {
                        $fail('Nhân viên này đã được giao KPI này rồi.');
                    }
                },
            ],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'kpi_id.required' => 'Vui lòng chọn mẫu KPI.',
            'kpi_id.exists' => 'Mẫu KPI không hợp lệ.',
            'employee_id.required' => 'Vui lòng chọn nhân viên.',
            'employee_id.exists' => 'Nhân viên không hợp lệ.',
            'note.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
        ];
    }
}
