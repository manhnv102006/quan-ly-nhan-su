<?php

namespace App\Services;

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

        return array_map(function (array $item) use ($unread, $pendingAdvances) {
            if (($item['route'] ?? null) === 'employee.notifications*') {
                $item['badge'] = $unread;
            }

            if (($item['key'] ?? null) === 'advances' && $pendingAdvances > 0) {
                $item['badge'] = $pendingAdvances;
            }

            if (! empty($item['children'])) {
                $item['children'] = array_map(function (array $child) use ($unread, $pendingAdvances) {
                    if (($child['route'] ?? null) === 'employee.notifications*') {
                        $child['badge'] = $unread;
                    }

                    if (($child['key'] ?? null) === 'advances-pending' && $pendingAdvances > 0) {
                        $child['badge'] = $pendingAdvances;
                    }

                    return $child;
                }, $item['children']);
            }

            return $item;
        }, $navigation);
    }
}
