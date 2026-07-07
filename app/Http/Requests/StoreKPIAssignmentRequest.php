<?php

namespace App\Http\Requests;

use App\Models\KPI;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreKPIAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $assignmentId = $this->route('assignment')?->id;

        return [
            'kpi_id' => [
                'required',
                'exists:kpis,id',
                Rule::unique('kpi_assignments', 'kpi_id')
                    ->where(function ($query) {
                        return $query->whereIn('status', ['pending', 'active']);
                    })
                    ->ignore($assignmentId),
            ],

            'manager_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $kpi = KPI::with('departments')->find($this->input('kpi_id'));
                    if (! $kpi) {
                        return;
                    }

                    $kpiDeptIds = $kpi->departments->pluck('id')->all();

                    if (empty($kpiDeptIds)) {
                        return;
                    }

                    $manager = User::with('employee')->find($value);
                    $managerDeptId = optional(optional($manager)->employee)->department_id;

                    if (! $managerDeptId || ! in_array($managerDeptId, $kpiDeptIds)) {
                        $fail('Manager được chọn không thuộc phòng ban áp dụng của KPI này.');
                    }
                },
            ],

            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function assignmentPayload(): array
    {
        $data = $this->validated();
        $kpi = KPI::with('departments')->findOrFail($data['kpi_id']);

        $target = $kpi->numericTargetForAssignment();

        if ($target === null) {
            throw ValidationException::withMessages([
                'kpi_id' => 'KPI này chưa có mục tiêu hợp lệ. Vui lòng cập nhật KPI trước khi giao.',
            ]);
        }

        if (! $kpi->hasAssignmentSchedule()) {
            throw ValidationException::withMessages([
                'kpi_id' => 'KPI này chưa có ngày bắt đầu và ngày kết thúc. Vui lòng cập nhật KPI trước khi giao.',
            ]);
        }

        if ($kpi->is_percent_unit && ($target < 0 || $target > 100)) {
            throw ValidationException::withMessages([
                'kpi_id' => 'Mục tiêu phần trăm của KPI phải từ 0 đến 100.',
            ]);
        }

        return [
            ...$data,
            'target' => $target,
            'start_date' => $kpi->start_date->toDateString(),
            'end_date' => $kpi->end_date->toDateString(),
        ];
    }

    public function messages(): array
    {
        return [
            'kpi_id.required' => 'Vui lòng chọn KPI.',
            'kpi_id.exists' => 'KPI không hợp lệ.',
            'kpi_id.unique' => 'KPI này đã được giao và chưa hoàn thành.',
            'manager_id.required' => 'Vui lòng chọn Manager.',
            'manager_id.exists' => 'Manager không hợp lệ.',
        ];
    }
}
