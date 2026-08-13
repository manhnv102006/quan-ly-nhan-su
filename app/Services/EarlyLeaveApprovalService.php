<?php

namespace App\Services;

use App\Models\EarlyLeaveRequest;
use App\Models\EarlyLeaveRequestHistory;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EarlyLeaveApprovalService
{
    public function logSubmitted(EarlyLeaveRequest $earlyLeaveRequest, int $actorId): void
    {
        EarlyLeaveRequestHistory::create([
            'early_leave_request_id' => $earlyLeaveRequest->id,
            'actor_id' => $actorId,
            'action' => 'submitted',
            'note' => 'Nhân viên gửi đơn xin về sớm.',
            'processed_at' => now(),
        ]);
    }

    public function approve(EarlyLeaveRequest $earlyLeaveRequest, int $actorId, ?Employee $manager = null): void
    {
        $this->assertActorAuthorized($earlyLeaveRequest, $actorId, $manager);
        $this->assertPending($earlyLeaveRequest);

        DB::transaction(function () use ($earlyLeaveRequest, $actorId) {
            $earlyLeaveRequest->update([
                'status' => EarlyLeaveRequest::STATUS_APPROVED,
                'approved_by' => $actorId,
                'approved_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
                'reject_reason' => null,
            ]);

            EarlyLeaveRequestHistory::create([
                'early_leave_request_id' => $earlyLeaveRequest->id,
                'actor_id' => $actorId,
                'action' => 'approved',
                'note' => null,
                'processed_at' => now(),
            ]);
        });
    }

    public function reject(EarlyLeaveRequest $earlyLeaveRequest, int $actorId, ?Employee $manager, string $reason): void
    {
        $this->assertActorAuthorized($earlyLeaveRequest, $actorId, $manager);
        $this->assertPending($earlyLeaveRequest);

        DB::transaction(function () use ($earlyLeaveRequest, $actorId, $reason) {
            $earlyLeaveRequest->update([
                'status' => EarlyLeaveRequest::STATUS_REJECTED,
                'approved_by' => null,
                'approved_at' => null,
                'rejected_by' => $actorId,
                'rejected_at' => now(),
                'reject_reason' => $reason,
            ]);

            EarlyLeaveRequestHistory::create([
                'early_leave_request_id' => $earlyLeaveRequest->id,
                'actor_id' => $actorId,
                'action' => 'rejected',
                'note' => $reason,
                'processed_at' => now(),
            ]);
        });
    }

    protected function assertPending(EarlyLeaveRequest $earlyLeaveRequest): void
    {
        if (! $earlyLeaveRequest->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Chỉ xử lý đơn ở trạng thái chờ duyệt.',
            ]);
        }
    }

    protected function assertActorAuthorized(EarlyLeaveRequest $earlyLeaveRequest, int $actorId, ?Employee $manager): void
    {
        $user = User::find($actorId);
        $earlyLeaveRequest->loadMissing('employee.user');
        $requiresAdminApproval = $earlyLeaveRequest->employee?->requiresAdminApproval() ?? false;

        if ($requiresAdminApproval) {
            if (! $user?->isAdmin()) {
                throw ValidationException::withMessages(['authorization' => 'Đơn về sớm của quản lý/kế toán chỉ Admin mới được duyệt hoặc từ chối.']);
            }

            return;
        }

        if ($user?->isAdmin()) {
            throw ValidationException::withMessages(['authorization' => 'Admin chỉ được duyệt đơn về sớm của quản lý/kế toán, không được duyệt đơn của nhân viên.']);
        }

        if (! $user?->isManager()) {
            throw ValidationException::withMessages(['authorization' => 'Chỉ quản lý hoặc admin mới được duyệt hoặc từ chối đơn về sớm.']);
        }

        if (! $manager) {
            throw ValidationException::withMessages(['authorization' => 'Tài khoản quản lý chưa liên kết hồ sơ nhân viên. Vui lòng liên hệ quản trị để được hỗ trợ.']);
        }

        if ($earlyLeaveRequest->employee?->user_id === $user->id) {
            throw ValidationException::withMessages(['authorization' => 'Bạn không thể tự duyệt đơn về sớm của chính mình.']);
        }

        if (! $earlyLeaveRequest->employee?->isManagedBy($manager)) {
            throw ValidationException::withMessages(['authorization' => 'Bạn không có quyền xử lý đơn về sớm này.']);
        }
    }
}
