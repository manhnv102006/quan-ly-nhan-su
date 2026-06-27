<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    public function create(User $sender, array $data, array $userIds): Notification
    {
        return DB::transaction(function () use ($sender, $data, $userIds) {
            $notification = Notification::create([
                'title' => $data['title'],
                'content' => $data['content'],
                'type' => $data['type'],
                'sender_id' => $sender->id,
            ]);

            $now = now();
            $rows = collect($userIds)
                ->unique()
                ->values()
                ->map(fn (int $userId) => [
                    'notification_id' => $notification->id,
                    'user_id' => $userId,
                    'is_read' => false,
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all();

            NotificationUser::insert($rows);

            return $notification;
        });
    }

    public function activeRecipientIds(string $audience, array $userIds = [], array $departmentIds = []): array
    {
        if ($audience === 'all') {
            return User::query()
                ->where('status', 'active')
                ->pluck('id')
                ->all();
        }

        if ($audience === 'departments') {
            $employeeUserIds = Employee::query()
                ->whereIn('department_id', $departmentIds)
                ->whereNotNull('user_id')
                ->pluck('user_id');

            $managerEmployeeIds = Department::query()
                ->whereIn('id', $departmentIds)
                ->whereNotNull('manager_id')
                ->pluck('manager_id');

            $managerUserIds = Employee::query()
                ->whereIn('id', $managerEmployeeIds)
                ->whereNotNull('user_id')
                ->pluck('user_id');

            $recipientIds = $employeeUserIds
                ->merge($managerUserIds)
                ->unique()
                ->values()
                ->all();

            if ($recipientIds === []) {
                return [];
            }

            return User::query()
                ->where('status', 'active')
                ->whereIn('id', $recipientIds)
                ->pluck('id')
                ->all();
        }

        return User::query()
            ->where('status', 'active')
            ->whereIn('id', $userIds)
            ->pluck('id')
            ->all();
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
