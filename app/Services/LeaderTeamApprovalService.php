<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaderTeamApprovalService
{
    public function __construct(
        private readonly LeaderScopeService $scope,
        private readonly NotificationService $notifications,
    ) {
    }

    public function approveLeave(LeaveRequest $leaveRequest, Employee $leader, int $actorId): void
    {
        $this->assertLeaveActionable($leaveRequest, $leader);

        DB::transaction(function () use ($leaveRequest, $leader, $actorId) {
            $leaveRequest->update([
                'leader_approved_by' => $actorId,
                'leader_approved_at' => now(),
            ]);

            LeaveRequestHistory::create([
                'leave_request_id' => $leaveRequest->id,
                'actor_id' => $actorId,
                'action' => 'leader_approved',
                'note' => 'Trưởng nhóm đã duyệt bước 1, chuyển Quản lý phê duyệt.',
            ]);

            $employeeUserId = $leaveRequest->employee?->user_id;
            if ($employeeUserId) {
                $this->notifications->sendToUser(
                    $employeeUserId,
                    'Đơn nghỉ phép đã được Trưởng nhóm duyệt',
                    'Đơn nghỉ phép của bạn đã được Trưởng nhóm duyệt bước 1 và đang chờ Quản lý phê duyệt.',
                    $actorId,
                );
            }

            $this->notifyDepartmentManager($leader, $leaveRequest->employee, 'Đơn nghỉ phép chờ duyệt bước 2', sprintf(
                'Trưởng nhóm %s đã duyệt đơn nghỉ phép của %s. Vui lòng phê duyệt bước 2.',
                $leader->full_name,
                $leaveRequest->employee?->full_name ?? 'nhân viên',
            ), $actorId);
        });
    }

    public function rejectLeave(LeaveRequest $leaveRequest, Employee $leader, int $actorId, string $reason): void
    {
        $this->assertLeaveActionable($leaveRequest, $leader);

        DB::transaction(function () use ($leaveRequest, $actorId, $reason) {
            $leaveRequest->update([
                'status' => LeaveRequest::STATUS_REJECTED,
                'reject_reason' => $reason,
                'rejected_by' => $actorId,
                'rejected_at' => now(),
            ]);

            LeaveRequestHistory::create([
                'leave_request_id' => $leaveRequest->id,
                'actor_id' => $actorId,
                'action' => 'leader_rejected',
                'note' => $reason,
            ]);

            $employeeUserId = $leaveRequest->employee?->user_id;
            if ($employeeUserId) {
                $this->notifications->sendToUser(
                    $employeeUserId,
                    'Đơn nghỉ phép bị Trưởng nhóm từ chối',
                    'Đơn nghỉ phép của bạn đã bị Trưởng nhóm từ chối. Lý do: '.$reason,
                    $actorId,
                );
            }
        });
    }

    public function approveOvertime(OvertimeRequest $overtimeRequest, Employee $leader, int $actorId): void
    {
        $this->assertOvertimeActionable($overtimeRequest, $leader);

        DB::transaction(function () use ($overtimeRequest, $leader, $actorId) {
            $overtimeRequest->update([
                'leader_approved_by' => $actorId,
                'leader_approved_at' => now(),
            ]);

            OvertimeRequestHistory::create([
                'overtime_request_id' => $overtimeRequest->id,
                'actor_id' => $actorId,
                'action' => 'leader_approved',
                'processed_at' => now(),
            ]);

            $employeeUserId = $overtimeRequest->employee?->user_id;
            if ($employeeUserId) {
                $this->notifications->sendToUser(
                    $employeeUserId,
                    'Đơn tăng ca đã được Trưởng nhóm duyệt',
                    'Đơn tăng ca ngày '.$overtimeRequest->work_date?->format('d/m/Y').' đã được Trưởng nhóm duyệt bước 1 và đang chờ Quản lý phê duyệt.',
                    $actorId,
                );
            }

            $this->notifyDepartmentManager($leader, $overtimeRequest->employee, 'Đơn tăng ca chờ duyệt bước 2', sprintf(
                'Trưởng nhóm %s đã duyệt đơn tăng ca của %s. Vui lòng phê duyệt bước 2.',
                $leader->full_name,
                $overtimeRequest->employee?->full_name ?? 'nhân viên',
            ), $actorId);
        });
    }

    public function rejectOvertime(OvertimeRequest $overtimeRequest, Employee $leader, int $actorId, string $reason): void
    {
        $this->assertOvertimeActionable($overtimeRequest, $leader);

        DB::transaction(function () use ($overtimeRequest, $actorId, $reason) {
            $overtimeRequest->update([
                'status' => OvertimeRequest::STATUS_REJECTED,
                'reject_reason' => $reason,
            ]);

            OvertimeRequestHistory::create([
                'overtime_request_id' => $overtimeRequest->id,
                'actor_id' => $actorId,
                'action' => 'leader_rejected',
                'processed_at' => now(),
            ]);

            $employeeUserId = $overtimeRequest->employee?->user_id;
            if ($employeeUserId) {
                $this->notifications->sendToUser(
                    $employeeUserId,
                    'Đơn tăng ca bị Trưởng nhóm từ chối',
                    'Đơn tăng ca ngày '.$overtimeRequest->work_date?->format('d/m/Y').' đã bị Trưởng nhóm từ chối. Lý do: '.$reason,
                    $actorId,
                );
            }
        });
    }

    protected function assertLeaveActionable(LeaveRequest $leaveRequest, Employee $leader): void
    {
        $leaveRequest->loadMissing('employee');

        if (! $leaveRequest->employee || ! $this->scope->managesEmployee($leader, $leaveRequest->employee)) {
            abort(403, 'Bạn chỉ được xử lý đơn nghỉ phép của thành viên trong nhóm.');
        }

        if (! $leaveRequest->isAwaitingLeaderApproval()) {
            throw ValidationException::withMessages([
                'status' => 'Chỉ duyệt đơn đang chờ Trưởng nhóm phê duyệt bước 1.',
            ]);
        }
    }

    protected function assertOvertimeActionable(OvertimeRequest $overtimeRequest, Employee $leader): void
    {
        $overtimeRequest->loadMissing('employee');

        if (! $overtimeRequest->employee || ! $this->scope->managesEmployee($leader, $overtimeRequest->employee)) {
            abort(403, 'Bạn chỉ được xử lý đơn tăng ca của thành viên trong nhóm.');
        }

        if (! $overtimeRequest->isAwaitingLeaderApproval()) {
            throw ValidationException::withMessages([
                'status' => 'Chỉ duyệt đơn đang chờ Trưởng nhóm phê duyệt bước 1.',
            ]);
        }
    }

    protected function notifyDepartmentManager(Employee $leader, ?Employee $employee, string $title, string $content, int $actorId): void
    {
        $leader->loadMissing('department.manager');
        $employee?->loadMissing('department.manager');

        $managerUserId = $leader->department?->manager?->user_id
            ?? $employee?->department?->manager?->user_id;

        if ($managerUserId) {
            $this->notifications->sendToUser($managerUserId, $title, $content, $actorId);
        }
    }
}
