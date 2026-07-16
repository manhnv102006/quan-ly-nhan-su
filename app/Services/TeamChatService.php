<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TeamChatMessage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TeamChatService
{
    public function __construct(
        private readonly LeaderEmployeeResolver $leaderResolver,
        private readonly NotificationService $notifications,
    ) {
    }

    public function canAccess(User $user): bool
    {
        return $this->resolveMembership($user) !== null;
    }

    /**
     * @return array{leader: Employee, member: Employee, role: string}|null
     */
    public function resolveMembership(User $user): ?array
    {
        if ($user->isLeader()) {
            $leader = $this->leaderResolver->resolve($user);

            return $leader ? [
                'leader' => $leader,
                'member' => $leader,
                'role' => 'leader',
            ] : null;
        }

        $employee = Employee::query()->where('user_id', $user->id)->first();

        if (! $employee?->manager_id) {
            return null;
        }

        $leader = Employee::query()->find($employee->manager_id);

        if (! $leader) {
            return null;
        }

        return [
            'leader' => $leader,
            'member' => $employee,
            'role' => 'member',
        ];
    }

    /**
     * @return Collection<int, Employee>
     */
    public function participants(Employee $leader): Collection
    {
        $members = Employee::query()
            ->managedByLeader($leader)
            ->orderBy('full_name')
            ->get();

        return collect([$leader])->merge($members)->unique('id')->values();
    }

    /**
     * @return Collection<int, TeamChatMessage>
     */
    public function messagesForTeam(Employee $leader, ?int $afterId = null, int $limit = 50): Collection
    {
        $query = TeamChatMessage::query()
            ->where('team_leader_id', $leader->id)
            ->with(['senderEmployee', 'senderUser']);

        if ($afterId) {
            return $query->where('id', '>', $afterId)->orderBy('id')->get();
        }

        return $query->latest()->limit($limit)->get()->sortBy('id')->values();
    }

    public function sendMessage(User $user, string $body): TeamChatMessage
    {
        $membership = $this->resolveMembership($user);

        if (! $membership) {
            abort(403, 'Bạn chưa thuộc nhóm chat nào.');
        }

        $body = trim($body);

        if ($body === '') {
            throw ValidationException::withMessages(['body' => 'Nội dung tin nhắn không được để trống.']);
        }

        return TeamChatMessage::create([
            'team_leader_id' => $membership['leader']->id,
            'sender_employee_id' => $membership['member']->id,
            'sender_user_id' => $user->id,
            'type' => TeamChatMessage::TYPE_MESSAGE,
            'body' => $body,
        ]);
    }

    public function sendAnnouncement(User $user, string $title, string $body): TeamChatMessage
    {
        if (! $user->isLeader()) {
            abort(403, 'Chỉ Trưởng nhóm mới được gửi thông báo nội bộ.');
        }

        $membership = $this->resolveMembership($user);

        if (! $membership) {
            abort(403, 'Bạn chưa liên kết hồ sơ trưởng nhóm.');
        }

        $title = trim($title);
        $body = trim($body);

        if ($title === '') {
            throw ValidationException::withMessages(['title' => 'Tiêu đề thông báo không được để trống.']);
        }

        if ($body === '') {
            throw ValidationException::withMessages(['body' => 'Nội dung thông báo không được để trống.']);
        }

        $message = TeamChatMessage::create([
            'team_leader_id' => $membership['leader']->id,
            'sender_employee_id' => $membership['member']->id,
            'sender_user_id' => $user->id,
            'type' => TeamChatMessage::TYPE_ANNOUNCEMENT,
            'title' => $title,
            'body' => $body,
        ]);

        $this->participants($membership['leader'])
            ->filter(fn (Employee $employee) => (int) $employee->id !== (int) $membership['member']->id)
            ->each(function (Employee $employee) use ($title, $body, $user) {
                if ($employee->user_id) {
                    $this->notifications->sendToUser(
                        (int) $employee->user_id,
                        'Thông báo nội bộ nhóm: '.$title,
                        $body,
                        $user->id,
                    );
                }
            });

        return $message;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function serializeMessages(Collection $messages, int $currentEmployeeId): array
    {
        return $messages->map(function (TeamChatMessage $message) use ($currentEmployeeId) {
            return [
                'id' => $message->id,
                'type' => $message->type,
                'title' => $message->title,
                'body' => $message->body,
                'sender_name' => $message->senderDisplayName(),
                'is_mine' => (int) $message->sender_employee_id === $currentEmployeeId,
                'is_announcement' => $message->isAnnouncement(),
                'time' => $message->created_at?->format('d/m/Y H:i'),
            ];
        })->values()->all();
    }
}
