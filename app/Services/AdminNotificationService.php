<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\User;
use App\Support\ManagerDepartmentResolver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminNotificationService
{
    /**
     * @return Collection<int, object>
     */
    public function recentForUser(User $user, int $limit = 5): Collection
    {
        return $this->baseQuery($user)->limit($limit)->get();
    }

    public function paginateForUser(User $user, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->applyFilters($this->baseQuery($user), $filters);

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

    public function findForUser(User $user, int $notificationId): ?object
    {
        return $this->baseQuery($user)
            ->leftJoin('users as senders', 'senders.id', '=', 'notifications.sender_id')
            ->leftJoin('departments', 'departments.id', '=', 'notifications.department_id')
            ->addSelect([
                'senders.name as sender_name',
                'notification_users.read_at',
                'departments.department_name',
            ])
            ->where('notifications.id', $notificationId)
            ->first();
    }

    public function markAsRead(User $user, int $notificationId): bool
    {
        if (! $this->userCanAccessNotification($user, $notificationId)) {
            return false;
        }

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
        $query = NotificationUser::query()
            ->where('user_id', $user->id)
            ->where('is_read', false);

        if ($user->isManager()) {
            $departmentId = ManagerDepartmentResolver::managedDepartmentId($user);

            $query->whereHas('notification', function ($inner) use ($departmentId) {
                if ($departmentId) {
                    $inner->where(function ($scope) use ($departmentId) {
                        $scope->where('department_id', $departmentId)
                            ->orWhereNull('department_id');
                    });

                    return;
                }

                $inner->whereNull('department_id');
            });
        }

        return $query->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function create(User $sender, array $data, array $userIds): Notification
    {
        return $this->persistNotification(array_merge($data, [
            'sender_id' => $sender->id,
        ]), $userIds);
    }

    public function schedule(User $sender, array $data, array $schedulePayload, \DateTimeInterface $scheduledAt): Notification
    {
        return Notification::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'type' => $data['type'],
            'sender_id' => $sender->id,
            'department_id' => $data['department_id'] ?? null,
            'delivery_status' => Notification::STATUS_SCHEDULED,
            'scheduled_at' => $scheduledAt,
            'schedule_payload' => $schedulePayload,
        ]);
    }

    /**
     * @return Collection<int, Notification>
     */
    public function pendingScheduledForUser(User $user): Collection
    {
        return Notification::query()
            ->where('sender_id', $user->id)
            ->where('delivery_status', Notification::STATUS_SCHEDULED)
            ->orderBy('scheduled_at')
            ->get();
    }

    /**
     * @return array{sent: int, failed: int}
     */
    public function dispatchDueScheduledNotifications(): array
    {
        $sent = 0;
        $failed = 0;

        DB::transaction(function () use (&$sent, &$failed) {
            Notification::query()
                ->where('delivery_status', Notification::STATUS_SCHEDULED)
                ->where('scheduled_at', '<=', now())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->each(function (Notification $notification) use (&$sent, &$failed) {
                    $recipientIds = $this->resolveRecipientsFromSchedulePayload($notification);

                    if ($recipientIds === []) {
                        $notification->update(['delivery_status' => Notification::STATUS_FAILED]);
                        $failed++;

                        return;
                    }

                    $this->attachRecipients($notification, $recipientIds);
                    $notification->update([
                        'delivery_status' => Notification::STATUS_SENT,
                        'sent_at' => now(),
                    ]);

                    $sent++;
                });
        });

        return compact('sent', 'failed');
    }

    public function createSystem(array $data, array $userIds): ?Notification
    {
        $userIds = $this->filterActiveUserIds($userIds);

        if ($userIds === []) {
            return null;
        }

        return $this->persistNotification(array_merge($data, [
            'sender_id' => $data['sender_id'] ?? null,
        ]), $userIds);
    }

    public function filterActiveUserIds(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        return User::query()
            ->where('status', 'active')
            ->whereIn('id', $userIds)
            ->pluck('id')
            ->all();
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
            return $this->recipientIdsForDepartments($departmentIds);
        }

        return User::query()
            ->where('status', 'active')
            ->whereIn('id', $userIds)
            ->pluck('id')
            ->all();
    }

    public function recipientIdsForDepartments(array $departmentIds): array
    {
        if ($departmentIds === []) {
            return [];
        }

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

    public function filterDepartmentRecipientIds(int $departmentId, array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        $allowed = $this->recipientIdsForDepartments([$departmentId]);

        return array_values(array_intersect($allowed, array_map('intval', $userIds)));
    }

    /**
     * @return Collection<int, User>
     */
    public function departmentMemberUsers(int $departmentId): Collection
    {
        $recipientIds = $this->recipientIdsForDepartments([$departmentId]);

        if ($recipientIds === []) {
            return collect();
        }

        return User::query()
            ->with('role')
            ->where('status', 'active')
            ->whereIn('id', $recipientIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role_id']);
    }

    private function persistNotification(array $data, array $userIds): Notification
    {
        $userIds = $this->filterActiveUserIds($userIds);

        if ($userIds === []) {
            throw new \InvalidArgumentException('Không có người nhận hợp lệ.');
        }

        return DB::transaction(function () use ($data, $userIds) {
            $notification = Notification::create([
                'title' => $data['title'],
                'content' => $data['content'],
                'type' => $data['type'],
                'sender_id' => $data['sender_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'delivery_status' => Notification::STATUS_SENT,
                'sent_at' => now(),
            ]);

            $this->attachRecipients($notification, $userIds);

            return $notification;
        });
    }

    private function attachRecipients(Notification $notification, array $userIds): void
    {
        $userIds = $this->filterActiveUserIds($userIds);

        if ($userIds === []) {
            throw new \InvalidArgumentException('Không có người nhận hợp lệ.');
        }

        $now = now();
        $rows = collect($userIds)
            ->unique()
            ->values()
            ->map(fn ($userId) => [
                'notification_id' => $notification->id,
                'user_id' => (int) $userId,
                'is_read' => false,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        NotificationUser::insert($rows);
    }

    private function resolveRecipientsFromSchedulePayload(Notification $notification): array
    {
        $payload = $notification->schedule_payload ?? [];
        $audience = $payload['audience'] ?? 'all';

        if ($audience === 'all' && $notification->department_id) {
            return $this->recipientIdsForDepartments([$notification->department_id]);
        }

        return match ($audience) {
            'all' => $this->activeRecipientIds('all'),
            'departments' => $this->recipientIdsForDepartments($payload['department_ids'] ?? []),
            'selected' => $notification->department_id
                ? $this->filterDepartmentRecipientIds($notification->department_id, $payload['user_ids'] ?? [])
                : $this->activeRecipientIds('selected', $payload['user_ids'] ?? []),
            default => [],
        };
    }

    private function baseQuery(User $user)
    {
        $query = Notification::query()
            ->join('notification_users', 'notification_users.notification_id', '=', 'notifications.id')
            ->where('notification_users.user_id', $user->id)
            ->select([
                'notifications.id',
                'notifications.title',
                'notifications.content',
                'notifications.type',
                'notifications.department_id',
                'notifications.created_at',
                'notification_users.id as notification_user_id',
                'notification_users.is_read',
            ])
            ->orderByDesc('notifications.created_at');

        if ($user->isManager()) {
            $this->applyManagerNotificationScope($query, $user);
        }

        return $query;
    }

    private function applyManagerNotificationScope($query, User $user): void
    {
        $departmentId = ManagerDepartmentResolver::managedDepartmentId($user);

        if ($departmentId) {
            $query->where(function ($inner) use ($departmentId) {
                $inner->where('notifications.department_id', $departmentId)
                    ->orWhereNull('notifications.department_id');
            });

            return;
        }

        $query->whereNull('notifications.department_id');
    }

    private function applyFilters($query, array $filters)
    {
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

        return $query;
    }

    private function userCanAccessNotification(User $user, int $notificationId): bool
    {
        $hasPivot = NotificationUser::query()
            ->where('user_id', $user->id)
            ->where('notification_id', $notificationId)
            ->exists();

        if (! $hasPivot) {
            return false;
        }

        if (! $user->isManager()) {
            return true;
        }

        $departmentId = ManagerDepartmentResolver::managedDepartmentId($user);
        $notificationQuery = Notification::query()->whereKey($notificationId);

        if ($departmentId) {
            $notificationQuery->where(function ($inner) use ($departmentId) {
                $inner->where('department_id', $departmentId)
                    ->orWhereNull('department_id');
            });
        } else {
            $notificationQuery->whereNull('department_id');
        }

        return $notificationQuery->exists();
    }
}
