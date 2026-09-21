<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Support\OvertimeLeaveConflictRules;
use App\Support\TimeInput;
use Carbon\Carbon;

class OvertimeLeaveConflictService
{
    /**
     * Thông tin nghỉ nửa ngày trong ngày tăng ca (để hướng dẫn OT ngoài khung giờ nghỉ).
     *
     * @return array{
     *     period: string,
     *     period_label: string,
     *     blocked_start: string,
     *     blocked_end: string,
     *     status: string,
     * }|null
     */
    public function halfDayLeaveOnDate(int $employeeId, string $workDate): ?array
    {
        $date = Carbon::parse($workDate)->toDateString();

        $leave = LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereIn('status', [
                LeaveRequest::STATUS_PENDING,
                LeaveRequest::STATUS_APPROVED,
            ])
            ->where('leave_type', 'half_day')
            ->overlappingPeriod($date, $date)
            ->first();

        if (! $leave || ! $leave->coversCalendarDay($date) || ! filled($leave->half_day_period)) {
            return null;
        }

        [$blockedStart, $blockedEnd] = OvertimeLeaveConflictRules::halfDayWindow((string) $leave->half_day_period);

        return [
            'period' => (string) $leave->half_day_period,
            'period_label' => $leave->halfDayPeriodLabel() ?? 'Nửa ngày',
            'blocked_start' => $blockedStart,
            'blocked_end' => $blockedEnd,
            'status' => (string) $leave->status,
        ];
    }

    /**
     * Trả về thông báo lỗi nếu OT trùng khoảng nghỉ; null nếu hợp lệ.
     */
    public function violationMessage(
        int $employeeId,
        string $workDate,
        string $startTime,
        string $endTime,
    ): ?string {
        $start = TimeInput::forInput($startTime);
        $end = TimeInput::forInput($endTime);
        $date = Carbon::parse($workDate)->toDateString();

        $leaves = LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereIn('status', [
                LeaveRequest::STATUS_PENDING,
                LeaveRequest::STATUS_APPROVED,
            ])
            ->whereIn('leave_type', OvertimeLeaveConflictRules::blockingLeaveTypes())
            ->overlappingPeriod($date, $date)
            ->get();

        foreach ($leaves as $leave) {
            if (! $leave->coversCalendarDay($date)) {
                continue;
            }

            if ($this->overlapsLeave($leave, $start, $end)) {
                return $this->messageFor($leave, $date);
            }
        }

        return null;
    }

    private function overlapsLeave(LeaveRequest $leave, string $otStart, string $otEnd): bool
    {
        if ($leave->leave_type === 'half_day') {
            $period = $leave->half_day_period;
            if (! filled($period)) {
                return true;
            }

            [$leaveStart, $leaveEnd] = OvertimeLeaveConflictRules::halfDayWindow((string) $period);

            return $this->timesOverlap($otStart, $otEnd, $leaveStart, $leaveEnd);
        }

        return true;
    }

    private function timesOverlap(string $startA, string $endA, string $startB, string $endB): bool
    {
        return $startA < $endB && $endA > $startB;
    }

    private function messageFor(LeaveRequest $leave, string $workDate): string
    {
        $dateLabel = Carbon::parse($workDate)->format('d/m/Y');

        if ($leave->leave_type === 'half_day') {
            $periodLabel = $leave->halfDayPeriodLabel() ?? 'nửa ngày';
            [$leaveStart, $leaveEnd] = OvertimeLeaveConflictRules::halfDayWindow((string) $leave->half_day_period);

            return sprintf(
                'Không thể tạo đơn tăng ca vì trùng %s (%s–%s) ngày %s. Chỉ được OT ngoài khoảng giờ đã nghỉ.',
                $periodLabel,
                $leaveStart,
                $leaveEnd,
                $dateLabel,
            );
        }

        return sprintf(
            'Không thể tạo đơn tăng ca vì bạn đang %s ngày %s (đơn %s).',
            mb_strtolower($leave->leaveTypeLabel()),
            $dateLabel,
            $leave->status === LeaveRequest::STATUS_PENDING ? 'chờ duyệt' : 'đã duyệt',
        );
    }
}
