<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Employee;
use App\Services\ManagerEmployeeResolver;
use Illuminate\Support\Facades\Auth;

trait ResolvesCurrentEmployee
{
    protected function currentManager(): Employee
    {
        return app(ManagerEmployeeResolver::class)->resolveOrFail(Auth::user());
    }

    protected function currentManagerOrNull(): ?Employee
    {
        return app(ManagerEmployeeResolver::class)->resolve(Auth::user());
    }

    protected function managedDepartmentId(Employee $manager): ?int
    {
        return Employee::managedDepartmentIdFor($manager);
    }
}
