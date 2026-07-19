<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Models\User;
use App\Services\ManagerEmployeeResolver;

class OvertimeRequestPolicy
{
    public function __construct(private readonly ManagerEmployeeResolver $managerResolver)
    {
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->isEmployee();
    }

    public function view(User $user, OvertimeRequest $overtimeRequest): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            return $this->managerCanManage($user, $overtimeRequest);
        }

        if ($user->isEmployee()) {
            return $overtimeRequest->employee?->user_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee() || $user->isManager();
    }

    public function update(User $user, OvertimeRequest $overtimeRequest): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isEmployee()) {
            return $overtimeRequest->employee?->user_id === $user->id
                && $overtimeRequest->status === OvertimeRequest::STATUS_PENDING;
        }

        return false;
    }

    public function delete(User $user, OvertimeRequest $overtimeRequest): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isEmployee()) {
            return $overtimeRequest->employee?->user_id === $user->id
                && $overtimeRequest->status === OvertimeRequest::STATUS_PENDING;
        }

        return false;
    }

    public function approve(User $user, OvertimeRequest $overtimeRequest): bool
    {
        return $this->decideApprovalAccess($user, $overtimeRequest);
    }

    public function reject(User $user, OvertimeRequest $overtimeRequest): bool
    {
        return $this->decideApprovalAccess($user, $overtimeRequest);
    }

    protected function decideApprovalAccess(User $user, OvertimeRequest $overtimeRequest): bool
    {
        $overtimeRequest->loadMissing('employee.user');
        $isFromManager = $overtimeRequest->employee?->user?->isManager() ?? false;

        if ($isFromManager) {
            return $user->isAdmin();
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isManager()) {
            return false;
        }

        return $this->managerCanManage($user, $overtimeRequest);
    }

    protected function managerCanManage(User $user, OvertimeRequest $overtimeRequest): bool
    {
        $overtimeRequest->loadMissing('employee.user');

        if ($overtimeRequest->employee?->user?->isManager()) {
            return false;
        }

        if ($overtimeRequest->employee?->user_id === $user->id) {
            return false;
        }

        $manager = $this->managerResolver->resolve($user);
        if (! $manager) {
            return false;
        }

        $departmentIds = Employee::departmentIdsForManagerApproval($manager);
        if ($departmentIds === []) {
            return false;
        }

        return $overtimeRequest->employee?->isInManagerDepartments($manager) ?? false;
    }
}
