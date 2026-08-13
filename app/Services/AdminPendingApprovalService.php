<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\EarlyLeaveRequest;
use App\Models\Interview;
use App\Models\JobPost;
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
     *     elevatedEarlyLeave: int,
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
            ->whereHas('employee', fn ($query) => $this->onlyElevatedRoleEmployees($query))
            ->count();

        $managerOvertime = OvertimeRequest::query()
            ->where('status', OvertimeRequest::STATUS_PENDING)
            ->whereHas('employee', fn ($query) => $this->onlyElevatedRoleEmployees($query))
            ->count();

        $elevatedEarlyLeave = EarlyLeaveRequest::query()
            ->where('status', EarlyLeaveRequest::STATUS_PENDING)
            ->requiresAdminApproval()
            ->count();

        $kpiAssignments = KPIAssignment::query()
            ->where('status', 'pending')
            ->count();

        $payroll = PayrollPeriod::query()
            ->whereIn('status', ['calculated', 'approved'])
            ->count();

        $recruitment = Candidate::query()
            ->where('status', Candidate::STATUS_PENDING_HIRE_APPROVAL)
            ->count()
            + \App\Models\JobPost::query()
                ->where('status', 'pending_approval')
                ->count();

        return [
            'managerLeave' => $managerLeave,
            'managerOvertime' => $managerOvertime,
            'elevatedEarlyLeave' => $elevatedEarlyLeave,
            'kpiAssignments' => $kpiAssignments,
            'payroll' => $payroll,
            'recruitment' => $recruitment,
            'total' => $managerLeave + $managerOvertime + $elevatedEarlyLeave + $kpiAssignments + $payroll + $recruitment,
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

        if ($counts['elevatedEarlyLeave'] > 0) {
            return route('admin.early-leave.index');
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
            'admin.early-leave.index' => $counts['elevatedEarlyLeave'],
            'admin.kpi-assignments.index' => $counts['kpiAssignments'],
            'admin.payroll-periods.index' => $counts['payroll'],
            'admin.recruitment' => $counts['recruitment'],
            default => 0,
        };
    }

    private function onlyElevatedRoleEmployees($query): void
    {
        $query->whereHas('user', function ($userQuery) {
            $userQuery->whereHas('role', fn ($roleQuery) => $roleQuery->whereIn('name', [Role::MANAGER, Role::ACCOUNTANT]));
        });
    }
}
