<?php

namespace App\Services;

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

        return array_map(function (array $item) use ($unread) {
            if (($item['route'] ?? null) === 'employee.notifications*') {
                $item['badge'] = $unread;
            }

            return $item;
        }, $navigation);
    }
}
