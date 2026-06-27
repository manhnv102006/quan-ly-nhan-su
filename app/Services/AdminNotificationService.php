<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AdminNotificationService
{
    /**
     * @return Collection<int, object{
     *     id: int,
     *     title: string,
     *     content: string,
     *     type: string,
     *     created_at: \Illuminate\Support\Carbon,
     *     is_read: bool,
     *     notification_user_id: int|null
     * }>
     */
    public function recentForUser(User $user, int $limit = 5): Collection
    {
        return $this->baseQuery($user)->limit($limit)->get();
    }

    public function paginateForUser(User $user, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->baseQuery($user);

        if (($filters['status'] ?? 'all') === 'unread') {
            $query->where('notification_users.is_read', false);
        } elseif (($filters['status'] ?? 'all') === 'read') {
            $query->where('notification_users.is_read', true);
        }

        if (! empty($filters['type'])) {
            $query->where('notifications.type', $filters['type']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($inner) use ($search) {
                $inner->where('notifications.title', 'like', "%{$search}%")
                    ->orWhere('notifications.content', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function unreadCount(User $user): int
    {
        return (int) $this->baseQuery($user)
            ->where('notification_users.is_read', false)
            ->count();
    }

    public function statsForUser(User $user): array
    {
        $all = $this->baseQuery($user);

        return [
            'total' => (clone $all)->count(),
            'unread' => (clone $all)->where('notification_users.is_read', false)->count(),
            'read' => (clone $all)->where('notification_users.is_read', true)->count(),
        ];
    }

    public function markAsRead(User $user, int $notificationId): bool
    {
        $pivot = NotificationUser::query()
            ->where('user_id', $user->id)
            ->where('notification_id', $notificationId)
            ->first();

        if (! $pivot) {
            return false;
        }

        if (! $pivot->is_read) {
            $pivot->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return true;
    }

    public function markAllAsRead(User $user): int
    {
        return NotificationUser::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    private function baseQuery(User $user)
    {
        return Notification::query()
            ->join('notification_users', 'notification_users.notification_id', '=', 'notifications.id')
            ->where('notification_users.user_id', $user->id)
            ->select([
                'notifications.id',
                'notifications.title',
                'notifications.content',
                'notifications.type',
                'notifications.created_at',
                'notification_users.id as notification_user_id',
                'notification_users.is_read',
            ])
            ->orderByDesc('notifications.created_at');
    }
}
