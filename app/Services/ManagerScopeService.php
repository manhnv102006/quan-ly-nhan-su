<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ManagerScopeService
{
    public function __construct(private readonly ManagerEmployeeResolver $resolver)
    {
    }

    public function resolveManagerEmployee(User $user): ?Employee
    {
        return $this->resolver->resolve($user);
    }

    public function resolveManagerEmployeeOrFail(User $user): Employee
    {
        return $this->resolver->resolveOrFail($user);
    }

    /**
     * @return list<int>
     */
    public function managedDepartmentIds(Employee $manager): array
    {
        return Department::query()
            ->where('manager_id', $manager->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function managedDepartmentId(Employee $manager): ?int
    {
        $ids = $this->managedDepartmentIds($manager);

        return $ids[0] ?? null;
    }

    /**
     * @return Builder<Employee>
     */
    public function managedEmployeesQuery(Employee $manager): Builder
    {
        return Employee::query()->managedByManager($manager);
    }

    public function managesEmployee(Employee $manager, Employee $employee): bool
    {
        return $employee->isManagedBy($manager);
    }

    /**
     * @return Collection<int, Employee>
     */
    public function managedEmployees(Employee $manager): Collection
    {
        return $this->managedEmployeesQuery($manager)->get();
    }
}
