<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;

class ManagerEmployeeResolver
{
    public function resolve(User $user): ?Employee
    {
        if ($employee = $user->employee) {
            return $employee;
        }

        $departmentManager = Employee::query()
            ->whereIn('id', Department::query()->whereNotNull('manager_id')->select('manager_id'))
            ->where(function ($query) use ($user) {
                $query->where('email', $user->email)
                    ->orWhere('full_name', $user->name);
            })
            ->first();

        if ($departmentManager && ! $departmentManager->user_id) {
            $departmentManager->update(['user_id' => $user->id]);

            return $departmentManager->fresh();
        }

        if ($departmentManager) {
            return $departmentManager;
        }

        $employeeCode = match ($user->username) {
            'manager' => 'EMP002',
            'admin' => 'EMP001',
            'employee' => 'EMP004',
            default => null,
        };

        if ($employeeCode) {
            $employee = Employee::where('employee_code', $employeeCode)
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
            ?? abort(403, 'Tài khoản quản lý chưa liên kết hồ sơ nhân viên. Vui lòng liên hệ quản trị để được hỗ trợ.');
    }
}
