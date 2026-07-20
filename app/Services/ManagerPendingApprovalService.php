<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeKPI;
use App\Models\KPIAssignment;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Role;
use App\Models\User;

class ManagerPendingApprovalService
{
    public function __construct(
        private readonly ManagerEmployeeResolver $managerResolver,
    ) {}

    /**
     * @return array{leave: int, overtime: int, kpi: int, total: int}
     */
    public function countsForUser(?User $user): array
    {
        if (! $user?->isManager()) {
            return $this->emptyCounts();
        }

        $manager = $this->managerResolver->resolve($user);

        if (! $manager) {
            return $this->emptyCounts();
        }

        return $this->countsForManager($manager);
    }

    /**
     * @return array{leave: int, overtime: int, kpi: int, total: int}
     */
    public function countsForManager(Employee $manager): array
    {
        $pendingLeaves = LeaveRequest::query()
            ->forManager($manager)
            ->where('status', LeaveRequest::STATUS_PENDING)
            ->whereHas('employee', fn ($query) => $this->excludeManagerEmployees($query))
            ->count();

        $pendingOvertimes = OvertimeRequest::query()
            ->forManager($manager)
            ->where('status', OvertimeRequest::STATUS_PENDING)
            ->count();

        $kpiActions = $this->kpiActionCountForManager($manager);

        return [
            'leave' => $pendingLeaves,
            'overtime' => $pendingOvertimes,
            'kpi' => $kpiActions,
            'total' => $pendingLeaves + $pendingOvertimes + $kpiActions,
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
                $item['children'] = array_map(
                    fn (array $child) => $this->applyBadgeToItem($child, $counts),
                    $item['children'],
                );

                $item['badge'] = collect($item['children'])->sum(fn (array $child) => (int) ($child['badge'] ?? 0));

                return $item;
            }

            return $this->applyBadgeToItem($item, $counts);
        }, $navigation);
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array{leave: int, overtime: int, kpi: int, total: int}  $counts
     * @return array<string, mixed>
     */
    private function applyBadgeToItem(array $item, array $counts): array
    {
        $route = $item['route'] ?? null;

        if ($route === 'manager.leave-requests*') {
            $item['badge'] = $counts['leave'];
        } elseif ($route === 'manager.overtime-requests*') {
            $item['badge'] = $counts['overtime'];
        } elseif ($route === 'manager.kpis*') {
            $item['badge'] = $counts['kpi'];
        } elseif (str_contains((string) ($item['href'] ?? ''), 'manager/kpis') || str_contains((string) ($item['href'] ?? ''), '#kpi')) {
            $item['badge'] = $counts['kpi'];
        } elseif (str_contains((string) ($item['href'] ?? ''), '#approvals')) {
            $item['badge'] = $counts['leave'] + $counts['overtime'];
        }

        return $item;
    }

    private function excludeManagerEmployees($query): void
    {
        $query->whereDoesntHave('user', function ($userQuery) {
            $userQuery->whereHas('role', fn ($roleQuery) => $roleQuery->where('name', Role::MANAGER));
        });
    }

    /**
     * @return array{leave: int, overtime: int, kpi: int, total: int}
     */
    private function emptyCounts(): array
    {
        return [
            'leave' => 0,
            'overtime' => 0,
            'kpi' => 0,
            'total' => 0,
        ];
    }

    private function kpiActionCountForManager(Employee $manager): int
    {
        $userId = $manager->user_id;

        if (! $userId) {
            return 0;
        }

        // KPI mới admin giao (chờ admin duyệt) — manager cần biết có KPI mới
        $newFromAdmin = KPIAssignment::query()
            ->where('manager_id', $userId)
            ->where('status', 'pending')
            ->count();

        // KPI đã duyệt, chưa giao xuống nhân viên
        $unassigned = KPIAssignment::query()
            ->where('manager_id', $userId)
            ->where('status', 'active')
            ->doesntHave('employeeKpis')
            ->count();

        // KPI nhân viên hoàn thành, chờ manager chấm điểm
        $needsReview = EmployeeKPI::query()
            ->where('status', EmployeeKPI::STATUS_COMPLETED)
            ->whereNull('score')
            ->whereHas('kpiAssignment', fn ($query) => $query->where('manager_id', $userId))
            ->count();

        return $newFromAdmin + $unassigned + $needsReview;
    }
}
