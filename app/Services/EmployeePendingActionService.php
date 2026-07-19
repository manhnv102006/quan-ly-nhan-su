<?php

namespace App\Services;

use App\Models\EmployeeKPI;
use App\Models\User;

class EmployeePendingActionService
{
    public function __construct(
        private readonly ManagerEmployeeResolver $employeeResolver,
    ) {}

    /**
     * @return array{kpis: int, total: int}
     */
    public function countsForUser(?User $user): array
    {
        if (! $user?->isEmployee()) {
            return $this->emptyCounts();
        }

        $employee = $this->employeeResolver->resolve($user);

        if (! $employee) {
            return $this->emptyCounts();
        }

        $kpis = EmployeeKPI::query()
            ->where('employee_id', $employee->id)
            ->whereIn('status', [
                EmployeeKPI::STATUS_PENDING,
                EmployeeKPI::STATUS_IN_PROGRESS,
            ])
            ->count();

        return [
            'kpis' => $kpis,
            'total' => $kpis,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $navigation
     * @return array<int, array<string, mixed>>
     */
    public function applyBadgesToNavigation(array $navigation, ?User $user): array
    {
        $counts = $this->countsForUser($user);

        return array_map(function (array $item) use ($counts) {
            if (! empty($item['children'])) {
                $item['children'] = array_map(function (array $child) use ($counts) {
                    if (($child['route'] ?? null) === 'employee.kpis.*') {
                        $child['badge'] = $counts['kpis'];
                    }

                    return $child;
                }, $item['children']);

                return $item;
            }

            if (($item['route'] ?? null) === 'employee.kpis.*') {
                $item['badge'] = $counts['kpis'];
            }

            return $item;
        }, $navigation);
    }

    /**
     * @return array{kpis: int, total: int}
     */
    private function emptyCounts(): array
    {
        return [
            'kpis' => 0,
            'total' => 0,
        ];
    }
}
