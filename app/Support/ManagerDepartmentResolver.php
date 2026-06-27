<?php

namespace App\Support;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;

final class ManagerDepartmentResolver
{
    public static function managedDepartment(User $user): ?Department
    {
        if (! $user->isManager()) {
            return null;
        }

        $employee = Employee::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $employee) {
            return null;
        }

        $managed = Department::query()
            ->where('manager_id', $employee->id)
            ->first();

        if ($managed) {
            return $managed;
        }

        if ($employee->department_id) {
            return Department::query()->find($employee->department_id);
        }

        return null;
    }

    public static function managedDepartmentId(User $user): ?int
    {
        return self::managedDepartment($user)?->id;
    }
}
