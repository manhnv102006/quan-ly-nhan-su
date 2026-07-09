<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Interview;
use App\Models\KPIAssignment;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollPeriod;
use App\Models\Role;
use App\Models\User;

class AdminPendingApprovalService
{
    /**
     * @return array{
     *     managerLeave: int,
     *     managerOvertime: int,
     *     kpiAssignments: int,
     *     payroll: int,
     *     recruitment: int,
     *     total: int
     * }
     */
    public function counts(): array
    {
        $managerLeave = LeaveRequest::query()
            ->where('status', LeaveRequest::STATUS_PENDING)
            ->whereHas('employee', fn ($query) => $this->onlyManagerRoleEmployees($query))
            ->count();

        $managerOvertime = OvertimeRequest::query()
            ->where('status', OvertimeRequest::STATUS_PENDING)
            ->whereHas('employee', fn ($query) => $this->onlyManagerRoleEmployees($query))
            ->count();

        $kpiAssignments = KPIAssignment::query()
            ->where('status', 'pending')
            ->count();

        $payroll = PayrollPeriod::query()
            ->whereIn('status', ['calculated', 'approved'])
            ->count();

        $recruitment = Candidate::query()
            ->where('status', 'new')
            ->count()
            + Interview::query()
                ->where('result', 'pending')
                ->count();

        return [
            'managerLeave' => $managerLeave,
            'managerOvertime' => $managerOvertime,
            'kpiAssignments' => $kpiAssignments,
            'payroll' => $payroll,
            'recruitment' => $recruitment,
            'total' => $managerLeave + $managerOvertime + $kpiAssignments + $payroll + $recruitment,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public function applyBadgesToMenuItems(array $items): array
    {
        $counts = $this->counts();

        return array_map(function (array $item) use ($counts) {
            if (isset($item['children'])) {
                $childBadgeSum = 0;

                $item['children'] = array_map(function (array $child) use ($counts, &$childBadgeSum) {
                    $badge = $this->badgeForRoute((string) ($child['route'] ?? ''), $counts);

                    if ($badge > 0) {
                        $child['badge'] = $badge;
                        $childBadgeSum += $badge;
                    }

                    return $child;
                }, $item['children']);

                if ($childBadgeSum > 0) {
                    $item['badge'] = $childBadgeSum;
                }
            } else {
                $badge = $this->badgeForRoute((string) ($item['route'] ?? ''), $counts);

                if ($badge > 0) {
                    $item['badge'] = $badge;
                }
            }

            return $item;
        }, $items);
    }

    public function primaryActionUrl(array $counts): string
    {
        if ($counts['managerLeave'] > 0) {
            return route('admin.leave-requests');
        }

        if ($counts['managerOvertime'] > 0) {
            return route('admin.overtime-requests.index');
        }

        if ($counts['kpiAssignments'] > 0) {
            return route('admin.kpi-assignments.index');
        }

        if ($counts['payroll'] > 0) {
            return route('admin.payroll-periods.index');
        }

        if ($counts['recruitment'] > 0) {
            return route('admin.recruitment');
        }

        return route('admin.dashboard');
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function badgeForRoute(string $route, array $counts): int
    {
        return match ($route) {
            'admin.leave-requests' => $counts['managerLeave'],
            'admin.overtime-requests.index' => $counts['managerOvertime'],
            'admin.kpi-assignments.index' => $counts['kpiAssignments'],
            'admin.payroll-periods.index' => $counts['payroll'],
            'admin.recruitment' => $counts['recruitment'],
            default => 0,
        };
    }

    private function onlyManagerRoleEmployees($query): void
    {
        $query->whereHas('user', function ($userQuery) {
            $userQuery->whereHas('role', fn ($roleQuery) => $roleQuery->where('name', Role::MANAGER));
        });
    }
}
