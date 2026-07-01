<?php

namespace App\Http\Requests;

use App\Models\KPI;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKPIAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kpi_id' => [
                'required',
                'exists:kpis,id',
                Rule::unique('kpi_assignments', 'kpi_id')
                    ->where(function ($query) {
                        return $query->whereIn('status', ['pending', 'active']);
                    }),
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

                    // KPI không gắn phòng ban nào -> cho phép mọi manager.
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

            'target' => [
                'required',
                'numeric',
                'min:0',
            ],

            'start_date' => [
                'required',
                'date',
                'before_or_equal:end_date',
            ],

            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],

            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
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

            'target.required' => 'Vui lòng nhập target.',
            'target.numeric' => 'Target phải là số.',
            'target.min' => 'Target không được âm.',

            'start_date.required' => 'Vui lòng nhập ngày bắt đầu.',
            'start_date.before_or_equal' => 'Ngày bắt đầu phải <= ngày kết thúc.',

            'end_date.required' => 'Vui lòng nhập ngày kết thúc.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải >= ngày bắt đầu.',
        ];
    }
}