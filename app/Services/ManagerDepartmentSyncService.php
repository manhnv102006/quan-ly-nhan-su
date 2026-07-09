<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;

class ManagerDepartmentSyncService
{
    public function syncAfterEmployeeSaved(Employee $employee): void
    {
        $employee->loadMissing('user.role');

        if ($employee->user?->isManager()) {
            $this->syncManagerAsDepartmentHead($employee);

            return;
        }

        $this->inheritDepartmentManager($employee);
    }

    public function syncAfterDepartmentManagerAssigned(Department $department, ?int $managerEmployeeId): void
    {
        if (! $managerEmployeeId) {
            return;
        }

        Employee::query()
            ->where('department_id', $department->id)
            ->where('id', '!=', $managerEmployeeId)
            ->whereNull('manager_id')
            ->whereDoesntHave('user.role', fn ($query) => $query->where('name', Role::MANAGER))
            ->update(['manager_id' => $managerEmployeeId]);
    }

    private function syncManagerAsDepartmentHead(Employee $manager): void
    {
        if (! $manager->department_id) {
            return;
        }

        $department = Department::query()->find($manager->department_id);

        if (! $department) {
            return;
        }

        if ($department->manager_id === null) {
            $department->update(['manager_id' => $manager->id]);
        }

        if ((int) $department->manager_id !== (int) $manager->id) {
            return;
        }

        Employee::query()
            ->where('department_id', $department->id)
            ->where('id', '!=', $manager->id)
            ->whereNull('manager_id')
            ->whereDoesntHave('user.role', fn ($query) => $query->where('name', Role::MANAGER))
            ->update(['manager_id' => $manager->id]);
    }

    private function inheritDepartmentManager(Employee $employee): void
    {
        if ($employee->manager_id || ! $employee->department_id) {
            return;
        }

        $departmentManagerId = Department::query()
            ->whereKey($employee->department_id)
            ->value('manager_id');

        if ($departmentManagerId) {
            $employee->update(['manager_id' => $departmentManagerId]);
        }
    }
}
