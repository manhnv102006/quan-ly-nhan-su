<?php

namespace App\Services;

use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OvertimeApprovalService
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function approve(OvertimeRequest $overtimeRequest, int $actorId): void
    {
        $this->assertPending($overtimeRequest);

        $this->processDecision(
            overtimeRequest: $overtimeRequest,
            actorId: $actorId,
            status: OvertimeRequest::STATUS_APPROVED,
            action: 'approved',
            title: 'Đơn tăng ca đã được phê duyệt',
            content: 'Đơn tăng ca ngày '.$overtimeRequest->work_date?->format('d/m/Y').' của bạn đã được quản lý phê duyệt.',
        );
    }

    public function reject(OvertimeRequest $overtimeRequest, int $actorId, string $reason): void
    {
        $this->assertPending($overtimeRequest);

        $this->processDecision(
            overtimeRequest: $overtimeRequest,
            actorId: $actorId,
            status: OvertimeRequest::STATUS_REJECTED,
            action: 'rejected',
            title: 'Đơn tăng ca đã bị từ chối',
            content: 'Đơn tăng ca ngày '.$overtimeRequest->work_date?->format('d/m/Y').' của bạn đã bị từ chối. Lý do: '.$reason,
            rejectReason: $reason,
        );
    }

    protected function assertPending(OvertimeRequest $overtimeRequest): void
    {
        if (! $overtimeRequest->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Chỉ xử lý đơn ở trạng thái chờ duyệt.',
            ]);
        }
    }

    protected function processDecision(
        OvertimeRequest $overtimeRequest,
        int $actorId,
        string $status,
        string $action,
        string $title,
        string $content,
        ?string $rejectReason = null
    ): void {
        DB::transaction(function () use ($overtimeRequest, $actorId, $status, $action, $title, $content, $rejectReason) {
            $overtimeRequest->update([
                'status' => $status,
                'approved_by' => $actorId,
                'approved_at' => now(),
                'reject_reason' => $rejectReason,
            ]);

            OvertimeRequestHistory::create([
                'overtime_request_id' => $overtimeRequest->id,
                'actor_id' => $actorId,
                'action' => $action,
                'processed_at' => now(),
            ]);

            $employeeUserId = $overtimeRequest->employee?->user_id;
            if ($employeeUserId) {
                $this->notifications->sendToUser($employeeUserId, $title, $content, $actorId);
            }
        });
    }
}
