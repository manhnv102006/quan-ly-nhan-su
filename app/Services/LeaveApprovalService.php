<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveApprovalService
{
    private const ANNUAL_LEAVE_ALLOWANCE = 12; // mặc định, có thể chuyển sang config hoặc cột employee khi cần

    public function approve(LeaveRequest $leaveRequest, int $actorId): void
    {
        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            throw ValidationException::withMessages(['status' => 'Chỉ xử lý đơn ở trạng thái chờ duyệt.']);
        }

        // quota phép năm
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

        // trùng thời gian
        $overlap = LeaveRequest::where('employee_id', $leaveRequest->employee_id)
            ->where('id', '!=', $leaveRequest->id)
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->whereDate('start_date', '<=', $leaveRequest->end_date)
            ->whereDate('end_date', '>=', $leaveRequest->start_date)
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages(['start_date' => 'Đơn này trùng thời gian với đơn đã duyệt khác.']);
        }

        DB::transaction(function () use ($leaveRequest, $actorId) {
            $leaveRequest->update([
                'status' => LeaveRequest::STATUS_APPROVED,
                'approved_by' => $actorId,
                'approved_at' => now(),
                'reject_reason' => null,
            ]);

            LeaveRequestHistory::create([
                'leave_request_id' => $leaveRequest->id,
                'actor_id' => $actorId,
                'action' => 'approved',
                'note' => null,
            ]);
        });
    }

    public function reject(LeaveRequest $leaveRequest, int $actorId, string $reason): void
    {
        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            throw ValidationException::withMessages(['status' => 'Chỉ xử lý đơn ở trạng thái chờ duyệt.']);
        }

        if (! $reason) {
            throw ValidationException::withMessages(['reject_reason' => 'Vui lòng nhập lý do từ chối.']);
        }

        DB::transaction(function () use ($leaveRequest, $actorId, $reason) {
            $leaveRequest->update([
                'status' => LeaveRequest::STATUS_REJECTED,
                'approved_by' => $actorId,
                'approved_at' => now(),
                'reject_reason' => $reason,
            ]);

            LeaveRequestHistory::create([
                'leave_request_id' => $leaveRequest->id,
                'actor_id' => $actorId,
                'action' => 'rejected',
                'note' => $reason,
            ]);
        });
    }
}
