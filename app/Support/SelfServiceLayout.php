<?php

namespace App\Support;

class SelfServiceLayout
{
    public static function component(?string $role = null): string
    {
        $role = $role ?? auth()->user()?->role?->name;

        return match ($role) {
            'admin' => 'admin-layout',
            'manager' => 'manager-layout',
            'leader' => 'leader-layout',
            'accountant' => 'accountant-layout',
            default => 'employee-layout',
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function navigation(?string $role = null): array
    {
        $role = $role ?? auth()->user()?->role?->name;

        return match ($role) {
            'manager' => ManagerNavigation::items(),
            'accountant' => AccountantNavigation::items(),
            'leader' => LeaderNavigation::items(),
            default => EmployeeNavigation::items(),
        };
    }
}
