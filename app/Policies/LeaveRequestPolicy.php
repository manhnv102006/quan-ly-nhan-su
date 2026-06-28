<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\ManagerEmployeeResolver;

class LeaveRequestPolicy
{
    public function __construct(private readonly ManagerEmployeeResolver $managerResolver)
    {
    }

    public function viewAsManager(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->managerCanManage($user, $leaveRequest);
    }

    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->managerCanManage($user, $leaveRequest);
    }

    public function reject(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->managerCanManage($user, $leaveRequest);
    }

    protected function managerCanManage(User $user, LeaveRequest $leaveRequest): bool
    {
        if (! $user->isManager()) {
            return false;
        }

        $manager = $this->managerResolver->resolve($user);
        if (! $manager) {
            return false;
        }

        $leaveRequest->loadMissing('employee');

        return $leaveRequest->employee?->isManagedBy($manager) ?? false;
    }
}
