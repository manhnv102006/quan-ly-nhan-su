<?php

namespace App\Policies;

use App\Models\OvertimeRequest;
use App\Models\User;

class OvertimeRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, OvertimeRequest $overtimeRequest): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function update(User $user, OvertimeRequest $overtimeRequest): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function delete(User $user, OvertimeRequest $overtimeRequest): bool
    {
        return $user->isAdmin();
    }
}
