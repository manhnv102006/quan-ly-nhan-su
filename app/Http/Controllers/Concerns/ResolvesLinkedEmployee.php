<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Employee;
use App\Services\ManagerEmployeeResolver;
use Illuminate\Support\Facades\Auth;

trait ResolvesLinkedEmployee
{
    protected function linkedEmployee(): Employee
    {
        $user = Auth::user();

        $employee = $user->employee ?? Employee::query()->where('user_id', $user->id)->first();

        if (! $employee && $user->role?->name === 'manager') {
            $employee = app(ManagerEmployeeResolver::class)->resolve($user);
        }

        if (! $employee) {
            abort(403, 'Tài khoản của bạn chưa được liên kết với hồ sơ nhân viên.');
        }

        return $employee;
    }
}
