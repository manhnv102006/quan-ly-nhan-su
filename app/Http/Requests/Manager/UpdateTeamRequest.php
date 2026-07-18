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
            'leader_employee_id' => [
                'nullable',
                'integer',
                'exists:employees,id',
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }

                    $manager = app(ManagerScopeService::class)->resolveManagerEmployee(Auth::user());
                    $leader = Employee::query()->with('user.role')->find($value);

                    if (! $manager || ! $leader || ! app(ManagerScopeService::class)->managesEmployee($manager, $leader)) {
                        $fail('Trưởng nhóm không thuộc phòng ban của bạn.');

                        return;
                    }

                    if (! $leader->user?->isLeader()) {
                        $fail('Nhân viên được chọn phải có vai trò Trưởng nhóm trong phòng ban.');
                    }
                },
            ],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
