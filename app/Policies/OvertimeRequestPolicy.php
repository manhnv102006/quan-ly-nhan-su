<?php

namespace App\Policies;

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
        return $user->isAdmin() || $user->isEmployee();
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
        if (! $user->isManager()) {
            return false;
        }

        return $this->managerCanManage($user, $overtimeRequest);
    }

    public function reject(User $user, OvertimeRequest $overtimeRequest): bool
    {
        return $this->approve($user, $overtimeRequest);
    }

    protected function managerCanManage(User $user, OvertimeRequest $overtimeRequest): bool
    {
        $manager = $this->managerResolver->resolve($user);
        if (! $manager) {
            return false;
        }

        $overtimeRequest->loadMissing('employee');

        return $overtimeRequest->employee?->isManagedBy($manager) ?? false;
    }
}
