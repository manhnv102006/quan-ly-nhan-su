<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;

class LeaderEmployeeResolver
{
    public function resolve(User $user): ?Employee
    {
        $user->loadMissing('employee');

        if ($user->employee) {
            return $user->employee;
        }

        $linkedEmployee = Employee::query()
            ->where('user_id', $user->id)
            ->first();

        if ($linkedEmployee) {
            return $linkedEmployee;
        }

        $employeeCode = match ($user->username) {
            'leader' => 'EMP003',
            default => null,
        };

        if ($employeeCode) {
            $employee = Employee::query()
                ->where('employee_code', $employeeCode)
                ->where(function ($query) use ($user) {
                    $query->whereNull('user_id')->orWhere('user_id', $user->id);
                })
                ->first();

            if ($employee) {
                if (! $employee->user_id) {
                    $employee->update(['user_id' => $user->id]);
                }

                return $employee->fresh();
            }
        }

        return null;
    }

    public function resolveOrFail(User $user): Employee
    {
        return $this->resolve($user)
            ?? abort(403, 'Tài khoản trưởng nhóm chưa liên kết hồ sơ nhân viên. Vui lòng liên hệ quản trị.');
    }
}
