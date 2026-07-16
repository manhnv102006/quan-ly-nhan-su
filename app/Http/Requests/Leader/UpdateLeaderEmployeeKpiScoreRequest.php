<?php

namespace App\Http\Requests\Leader;

use App\Models\EmployeeKPI;
use App\Services\LeaderScopeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateLeaderEmployeeKpiScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var EmployeeKPI|null $employeeKpi */
        $employeeKpi = $this->route('employeeKpi');

        if (! Auth::user()?->isLeader() || ! $employeeKpi) {
            return false;
        }

        $leader = app(LeaderScopeService::class)->resolveLeaderEmployee(Auth::user());

        if (! $leader) {
            return false;
        }

        $employeeKpi->loadMissing(['kpiAssignment', 'employee']);

        if (! $employeeKpi->kpiAssignment || (int) $employeeKpi->kpiAssignment->leader_employee_id !== (int) $leader->id) {
            return false;
        }

        return $employeeKpi->employee
            && app(LeaderScopeService::class)->managesEmployee($leader, $employeeKpi->employee);
    }

    public function rules(): array
    {
        return [
            'leader_score' => ['required', 'integer', 'min:0', 'max:100'],
            'leader_review' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'leader_score.required' => 'Vui lòng nhập điểm KPI.',
            'leader_score.min' => 'Điểm KPI không được nhỏ hơn 0.',
            'leader_score.max' => 'Điểm KPI không được lớn hơn 100.',
        ];
    }
}
