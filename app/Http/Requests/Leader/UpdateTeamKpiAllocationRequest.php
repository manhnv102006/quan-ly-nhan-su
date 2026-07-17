<?php

namespace App\Http\Requests\Leader;

use App\Models\Employee;
use App\Models\EmployeeKPI;
use App\Models\KPIAssignment;
use App\Services\LeaderKpiService;
use App\Services\LeaderScopeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateTeamKpiAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var KPIAssignment|null $assignment */
        $assignment = $this->route('assignment');
        /** @var EmployeeKPI|null $employeeKpi */
        $employeeKpi = $this->route('employeeKpi');

        if (! Auth::user()->isLeader() || ! $assignment || ! $employeeKpi) {
            return false;
        }

        $leader = app(LeaderScopeService::class)->resolveLeaderEmployee(Auth::user());

        if (! $leader || (int) $assignment->leader_employee_id !== (int) $leader->id) {
            return false;
        }

        return (int) $employeeKpi->assignment_id === (int) $assignment->id;
    }

    public function rules(): array
    {
        /** @var KPIAssignment $assignment */
        $assignment = $this->route('assignment');

        return [
            'target' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'deadline' => [
                'required',
                'date',
                'before_or_equal:'.$assignment->end_date->format('Y-m-d'),
            ],
        ];
    }
}
