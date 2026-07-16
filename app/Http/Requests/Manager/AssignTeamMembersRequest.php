<?php

namespace App\Http\Requests\Manager;

use App\Models\Team;
use App\Services\ManagerScopeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AssignTeamMembersRequest extends FormRequest
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
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_ids.required' => 'Vui lòng chọn ít nhất một thành viên.',
        ];
    }
}
