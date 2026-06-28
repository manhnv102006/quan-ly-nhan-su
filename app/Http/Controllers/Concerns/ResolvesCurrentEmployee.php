<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

trait ResolvesCurrentEmployee
{
    protected function currentManager(): Employee
    {
        $user = Auth::user();

        if ($employee = $user->employee) {
            return $employee;
        }

        // Nhân viên được gán trưởng phòng ban (departments.manager_id)
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

        // Liên kết tài khoản demo theo username (chỉ khi hồ sơ chưa có user)
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

        abort(403, 'Không tìm thấy thông tin nhân viên quản lý. Vui lòng liên hệ quản trị để liên kết tài khoản với hồ sơ nhân viên.');
    }

    protected function managedDepartmentId(Employee $manager): ?int
    {
        return Department::where('manager_id', $manager->id)->value('id')
            ?? $manager->department_id;
    }
}
