<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;

/**
 * Kiểm tra hạn mức nghỉ do Admin cấu hình cho từng loại nghỉ phép.
 *
 * Chỉ chặn với các kiểu hạn mức đo được bằng ngày (ngày/năm, ngày/lần) và khi
 * Admin bật "Chặn khi vượt hạn mức" — các kiểu còn lại (số dư phép năm, số dư
 * ngày bù, lần/thai kỳ, tháng/lần, thỏa thuận) do quy trình khác quyết định.
 */
class LeaveTypeQuotaService
{
    public function violationMessage(
        Employee $employee,
        ?LeaveType $leaveType,
        Carbon $startDate,
        float $requestedDays,
        ?int $ignoreLeaveRequestId = null,
    ): ?string {
        if ($leaveType === null || ! $leaveType->quotaIsEnforced()) {
            return null;
        }

        $quota = (float) $leaveType->quota_days;

        if ($leaveType->quota_type === LeaveType::QUOTA_DAYS_PER_EVENT) {
            if ($requestedDays > $quota) {
                return sprintf(
                    '%s chỉ được nghỉ tối đa %s cho mỗi lần. Đơn của bạn là %s.',
                    $leaveType->name,
                    $this->formatDays($quota),
                    $this->formatDays($requestedDays),
                );
            }

            return null;
        }

        $year = (int) $startDate->year;
        $used = $this->usedDaysInYear($employee, $leaveType->code, $year, $ignoreLeaveRequestId);

        if ($used + $requestedDays <= $quota) {
            return null;
        }

        $remaining = max(0.0, $quota - $used);

        return sprintf(
            '%s có hạn mức %s trong năm %d. Bạn đã dùng %s (gồm đơn chờ duyệt), chỉ còn %s.',
            $leaveType->name,
            $this->formatDays($quota),
            $year,
            $this->formatDays($used),
            $this->formatDays($remaining),
        );
    }

    public function usedDaysInYear(
        Employee $employee,
        string $leaveTypeCode,
        int $year,
        ?int $ignoreLeaveRequestId = null,
    ): float {
        return (float) LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('leave_type', $leaveTypeCode)
            ->whereIn('status', [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_APPROVED])
            ->whereYear('start_date', $year)
            ->when($ignoreLeaveRequestId !== null, fn ($query) => $query->whereKeyNot($ignoreLeaveRequestId))
            ->sum('total_days');
    }

    private function formatDays(float $days): string
    {
        if (fmod($days, 1.0) === 0.0) {
            return ((int) $days).' ngày';
        }

        return number_format($days, 1, ',', '.').' ngày';
    }
}
