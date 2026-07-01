<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;

class ManagerEmployeeResolver
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

        $departmentHead = Employee::query()
            ->whereIn('id', Department::query()->whereNotNull('manager_id')->select('manager_id'))
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('email', $user->email)
                    ->orWhere('full_name', $user->name);
            })
            ->first();

        if ($departmentHead) {
            if (! $departmentHead->user_id) {
                $departmentHead->update(['user_id' => $user->id]);
            }

            return $departmentHead->fresh();
        }

        $employeeCode = match ($user->username) {
            'manager' => 'EMP002',
            'admin' => 'EMP001',
            'employee' => 'EMP004',
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
            ?? abort(403, 'Tài khoản quản lý chưa liên kết hồ sơ nhân viên. Vui lòng liên hệ quản trị để được hỗ trợ.');
    }
}
