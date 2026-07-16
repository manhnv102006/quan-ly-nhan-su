<?php

namespace App\Http\Requests\Manager;

use App\Models\Employee;
use App\Models\Team;
use App\Services\ManagerScopeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Team|null $team */
        $team = $this->route('team');
        $manager = app(ManagerScopeService::class)->resolveManagerEmployee(Auth::user());

        if (! $team || ! $manager) {
            return false;
        }

        $departmentId = app(ManagerScopeService::class)->managedDepartmentId($manager);

        return $departmentId && (int) $team->department_id === $departmentId;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'leader_employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
