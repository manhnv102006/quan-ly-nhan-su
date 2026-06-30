<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveApprovalService
{
    private const ANNUAL_LEAVE_ALLOWANCE = 12;

    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function approve(LeaveRequest $leaveRequest, int $actorId, Employee $manager): void
    {
        $this->assertManagerActor($actorId);
        $leaveRequest->authorizeManagerAction($manager);
        $this->assertPending($leaveRequest);

        if ($leaveRequest->leave_type === 'annual') {
            $year = $leaveRequest->start_date?->year ?? now()->year;
            $used = LeaveRequest::where('employee_id', $leaveRequest->employee_id)
                ->where('leave_type', 'annual')
                ->where('status', LeaveRequest::STATUS_APPROVED)
                ->whereYear('start_date', $year)
                ->sum('total_days');

            if (($used + $leaveRequest->total_days) > self::ANNUAL_LEAVE_ALLOWANCE) {
                throw ValidationException::withMessages(['total_days' => 'Số ngày phép năm không đủ để duyệt đơn này.']);
            }
        }

        $overlap = LeaveRequest::where('employee_id', $leaveRequest->employee_id)
            ->where('id', '!=', $leaveRequest->id)
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->whereDate('start_date', '<=', $leaveRequest->end_date)
            ->whereDate('end_date', '>=', $leaveRequest->start_date)
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages(['start_date' => 'Đơn này trùng thời gian với đơn đã duyệt khác.']);
        }

        $this->processDecision(
            leaveRequest: $leaveRequest,
            actorId: $actorId,
            status: LeaveRequest::STATUS_APPROVED,
            action: 'approved',
            title: 'Đơn nghỉ phép đã được phê duyệt',
            content: 'Đơn nghỉ phép từ '.$leaveRequest->start_date?->format('d/m/Y').' đến '.$leaveRequest->end_date?->format('d/m/Y').' của bạn đã được phê duyệt.',
        );
    }

    public function reject(LeaveRequest $leaveRequest, int $actorId, Employee $manager, string $reason): void
    {
        $this->assertManagerActor($actorId);
        $leaveRequest->authorizeManagerAction($manager);
        $this->assertPending($leaveRequest);

        $this->processDecision(
            leaveRequest: $leaveRequest,
            actorId: $actorId,
            status: LeaveRequest::STATUS_REJECTED,
            action: 'rejected',
            title: 'Đơn nghỉ phép đã bị từ chối',
            content: 'Đơn nghỉ phép từ '.$leaveRequest->start_date?->format('d/m/Y').' đến '.$leaveRequest->end_date?->format('d/m/Y').' của bạn đã bị từ chối. Lý do: '.$reason,
            rejectReason: $reason,
        );
    }

    protected function assertPending(LeaveRequest $leaveRequest): void
    {
        if (! $leaveRequest->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Chỉ xử lý đơn ở trạng thái chờ duyệt.',
            ]);
        }
    }

    protected function assertManagerActor(int $actorId): void
    {
        $user = User::find($actorId);

        if ($user?->isAdmin()) {
            abort(403, 'Admin chỉ được xem đơn nghỉ phép, không được duyệt hoặc từ chối.');
        }

        if (! $user?->isManager()) {
            abort(403, 'Chỉ quản lý mới được duyệt hoặc từ chối đơn nghỉ phép.');
        }
    }

    protected function processDecision(
        LeaveRequest $leaveRequest,
        int $actorId,
        string $status,
        string $action,
        string $title,
        string $content,
        ?string $rejectReason = null
    ): void {
        DB::transaction(function () use ($leaveRequest, $actorId, $status, $action, $title, $content, $rejectReason) {
            if ($status === LeaveRequest::STATUS_APPROVED) {
                $leaveRequest->update([
                    'status' => $status,
                    'approved_by' => $actorId,
                    'approved_at' => now(),
                    'reject_reason' => null,
                    'rejected_by' => null,
                    'rejected_at' => null,
                ]);
            } else {
                $leaveRequest->update([
                    'status' => $status,
                    'approved_by' => null,
                    'approved_at' => null,
                    'reject_reason' => $rejectReason,
                    'rejected_by' => $actorId,
                    'rejected_at' => now(),
                ]);
            }

            LeaveRequestHistory::create([
                'leave_request_id' => $leaveRequest->id,
                'actor_id' => $actorId,
                'action' => $action,
                'note' => $rejectReason,
            ]);

            $employeeUserId = $leaveRequest->employee?->user_id;
            if ($employeeUserId) {
                $this->notifications->sendToUser($employeeUserId, $title, $content, $actorId);
            }
        });
    }
}
