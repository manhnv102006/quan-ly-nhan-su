<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LeaderScopeService
{
    public function __construct(private readonly LeaderEmployeeResolver $resolver) {}

    public function resolveLeaderEmployee(User $user): ?Employee
    {
        return $this->resolver->resolve($user);
    }

    public function resolveLeaderEmployeeOrFail(User $user): Employee
    {
        return $this->resolver->resolveOrFail($user);
    }

    /**
     * @return Builder<Employee>
     */
    public function teamMembersQuery(Employee $leader): Builder
    {
        return Employee::query()->managedByLeader($leader);
    }

    public function managesEmployee(Employee $leader, Employee $employee): bool
    {
        return $employee->isDirectReportOf($leader);
    }

    /**
     * @return Collection<int, Employee>
     */
    public function teamMembers(Employee $leader): Collection
    {
        return $this->teamMembersQuery($leader)->get();
    }

    /**
     * @return list<int>
     */
    public function teamMemberIds(Employee $leader): array
    {
        return $this->teamMembersQuery($leader)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }
}
