<?php

namespace App\Services;

use App\Models\PayrollComplaint;
use App\Models\SalaryAdvance;
use App\Models\User;

class AccountantPendingActionService
{
    public function __construct(
        private readonly AdminNotificationService $notifications,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $navigation
     * @return array<int, array<string, mixed>>
     */
    public function applyBadgesToNavigation(array $navigation, ?User $user): array
    {
        if (! $user?->isAccountant()) {
            return $navigation;
        }

        $unread = $this->notifications->unreadCount($user);
        $pendingAdvances = SalaryAdvance::query()
            ->where('status', SalaryAdvance::STATUS_PENDING)
            ->count();
        $pendingPayrollComplaints = PayrollComplaint::query()
            ->where('status', PayrollComplaint::STATUS_PROCESSING)
            ->count();

        return array_map(function (array $item) use ($unread, $pendingAdvances, $pendingPayrollComplaints) {
            if (($item['route'] ?? null) === 'employee.notifications*') {
                $item['badge'] = $unread;
            }

            if (($item['key'] ?? null) === 'advances' && $pendingAdvances > 0) {
                $item['badge'] = $pendingAdvances;
            }

            if (($item['key'] ?? null) === 'payroll' && $pendingPayrollComplaints > 0) {
                $item['badge'] = ($item['badge'] ?? 0) + $pendingPayrollComplaints;
            }

            if (! empty($item['children'])) {
                $item['children'] = array_map(function (array $child) use ($unread, $pendingAdvances, $pendingPayrollComplaints) {
                    if (($child['route'] ?? null) === 'employee.notifications*') {
                        $child['badge'] = $unread;
                    }

                    if (($child['key'] ?? null) === 'advances-pending' && $pendingAdvances > 0) {
                        $child['badge'] = $pendingAdvances;
                    }

                    if (($child['key'] ?? null) === 'payroll-complaints-pending' && $pendingPayrollComplaints > 0) {
                        $child['badge'] = $pendingPayrollComplaints;
                    }

                    return $child;
                }, $item['children']);

                $childBadgeTotal = collect($item['children'])->sum(fn (array $child) => (int) ($child['badge'] ?? 0));
                if ($childBadgeTotal > 0) {
                    $item['badge'] = max((int) ($item['badge'] ?? 0), $childBadgeTotal);
                }
            }

            return $item;
        }, $navigation);
    }
}
