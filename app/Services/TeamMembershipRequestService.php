<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TeamMembershipRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeamMembershipRequestService
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function create(Employee $leader, int $actorUserId, array $data): TeamMembershipRequest
    {
        $employee = Employee::query()->findOrFail($data['employee_id']);
        $action = $data['action'];

        if ($action === TeamMembershipRequest::ACTION_ADD) {
            if ((int) $employee->manager_id === (int) $leader->id) {
                throw ValidationException::withMessages(['employee_id' => 'Nhân viên này đã thuộc nhóm của bạn.']);
            }

            if ($employee->manager_id !== null) {
                throw ValidationException::withMessages(['employee_id' => 'Nhân viên này đã thuộc nhóm khác.']);
            }

            if ((int) $employee->department_id !== (int) $leader->department_id) {
                throw ValidationException::withMessages(['employee_id' => 'Chỉ có thể đề xuất nhân viên cùng phòng ban với bạn.']);
            }
        } else {
            if (! $employee->isDirectReportOf($leader)) {
                throw ValidationException::withMessages(['employee_id' => 'Nhân viên này không thuộc nhóm của bạn.']);
            }
        }

        $exists = TeamMembershipRequest::query()
            ->where('leader_id', $leader->id)
            ->where('employee_id', $employee->id)
            ->where('status', TeamMembershipRequest::STATUS_PENDING)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['employee_id' => 'Đã có một đề xuất đang chờ duyệt cho nhân viên này.']);
        }

        $request = TeamMembershipRequest::create([
            'leader_id' => $leader->id,
            'employee_id' => $employee->id,
            'action' => $action,
            'reason' => $data['reason'] ?? null,
            'status' => TeamMembershipRequest::STATUS_PENDING,
            'requested_by' => $actorUserId,
        ]);

        $manager = $leader->department?->manager;
        if ($manager?->user_id) {
            $this->notifications->sendToUser(
                $manager->user_id,
                'Đề xuất thay đổi thành viên nhóm',
                "Trưởng nhóm {$leader->full_name} đã gửi đề xuất ".TeamMembershipRequest::ACTION_LABELS[$action]." cho nhân viên {$employee->full_name}.",
                $actorUserId,
            );
        }

        return $request;
    }

    public function approve(TeamMembershipRequest $request, int $actorUserId, string $note = null): void
    {
        $this->assertPending($request);

        DB::transaction(function () use ($request, $actorUserId, $note) {
            $employee = $request->employee;

            if ($request->action === TeamMembershipRequest::ACTION_ADD) {
                $employee->update(['manager_id' => $request->leader_id]);
            } else {
                $employee->update(['manager_id' => null]);
            }

            $request->update([
                'status' => TeamMembershipRequest::STATUS_APPROVED,
                'decided_by' => $actorUserId,
                'decided_at' => now(),
                'decision_note' => $note,
            ]);

            $this->notifyLeader($request, 'Đề xuất thay đổi thành viên nhóm đã được duyệt', 'Đề xuất '.$request->actionLabel().' nhân viên '.$employee->full_name.' của bạn đã được duyệt.');
        });
    }

    public function reject(TeamMembershipRequest $request, int $actorUserId, string $note): void
    {
        $this->assertPending($request);

        DB::transaction(function () use ($request, $actorUserId, $note) {
            $request->update([
                'status' => TeamMembershipRequest::STATUS_REJECTED,
                'decided_by' => $actorUserId,
                'decided_at' => now(),
                'decision_note' => $note,
            ]);

            $this->notifyLeader($request, 'Đề xuất thay đổi thành viên nhóm bị từ chối', 'Đề xuất '.$request->actionLabel().' nhân viên '.$request->employee->full_name.' của bạn đã bị từ chối. Lý do: '.$note);
        });
    }

    private function notifyLeader(TeamMembershipRequest $request, string $title, string $content): void
    {
        $leaderUserId = $request->leader?->user_id;

        if ($leaderUserId) {
            $this->notifications->sendToUser($leaderUserId, $title, $content, $request->decided_by);
        }
    }

    private function assertPending(TeamMembershipRequest $request): void
    {
        if (! $request->isPending()) {
            throw ValidationException::withMessages(['status' => 'Đề xuất này đã được xử lý.']);
        }
    }
}
