<?php

namespace App\Policies;

use App\Models\OvertimeRequest;
use App\Models\User;

class OvertimeRequestPolicy
{
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
            $managerDepartmentId = $user->employee?->department_id;
            $requestDepartmentId = $overtimeRequest->employee?->department_id;

            return $managerDepartmentId && $requestDepartmentId === $managerDepartmentId;
        }

        // Nhân viên chỉ xem được đơn của chính mình
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

        // Nhân viên chỉ sửa được đơn của mình khi pending
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

        // Nhân viên chỉ xóa được đơn của mình khi pending
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

        $managerDepartmentId = $user->employee?->department_id;
        $requestDepartmentId = $overtimeRequest->employee?->department_id;

        return $managerDepartmentId && $requestDepartmentId === $managerDepartmentId;
    }

    public function reject(User $user, OvertimeRequest $overtimeRequest): bool
    {
        return $this->approve($user, $overtimeRequest);
    }
}
