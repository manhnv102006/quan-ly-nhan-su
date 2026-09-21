<?php

namespace App\Support;

use App\Models\Employee;

/**
 * Quy định giới hạn số người cùng nghỉ phép trong một phòng ban (tính theo ngày làm việc).
 */
final class LeaveCapacityRules
{
    /** Nhân viên: tối đa 30% nhân sự đang làm việc của phòng ban. */
    public const RATIO_EMPLOYEE = 0.30;

    /** Quản lý và kế toán: tối đa 20%. */
    public const RATIO_MANAGER_ACCOUNTANT = 0.20;

    /** Đơn nghỉ từ mức này trở lên (ngày công) không tính vào giới hạn phòng ban. */
    public const LONG_LEAVE_EXEMPT_FROM_DAYS = 12;

    /** @deprecated Dùng LONG_LEAVE_EXEMPT_FROM_DAYS */
    public const CAPACITY_EXEMPT_ABOVE_DAYS = self::LONG_LEAVE_EXEMPT_FROM_DAYS;

    /**
     * Loại nghỉ theo luật — không bị giới hạn phòng ban (chỉ cảnh báo khi duyệt).
     *
     * @var list<string>
     */
    public const STATUTORY_CAPACITY_EXEMPT_LEAVE_TYPES = [
        'maternity',
        'sick',
        'bereavement',
        'wedding',
    ];

    public static function countsTowardDepartmentCapacity(string $leaveType, float $totalDays): bool
    {
        if (self::isStatutoryCapacityExemptLeaveType($leaveType)) {
            return false;
        }

        return $totalDays < self::LONG_LEAVE_EXEMPT_FROM_DAYS;
    }

    public static function isStatutoryCapacityExemptLeaveType(string $leaveType): bool
    {
        return in_array($leaveType, self::STATUTORY_CAPACITY_EXEMPT_LEAVE_TYPES, true);
    }

    public static function ratioFor(Employee $employee): float
    {
        $employee->loadMissing('user.role');

        return $employee->user?->isManager() || $employee->user?->isAccountant()
            ? self::RATIO_MANAGER_ACCOUNTANT
            : self::RATIO_EMPLOYEE;
    }

    public static function percentFor(Employee $employee): int
    {
        return self::toPercent(self::ratioFor($employee));
    }

    public static function roleLabelFor(Employee $employee): string
    {
        $employee->loadMissing('user.role');

        if ($employee->user?->isManager()) {
            return 'Quản lý';
        }

        if ($employee->user?->isAccountant()) {
            return 'Kế toán';
        }

        return 'Nhân viên';
    }

    public static function toPercent(float $ratio): int
    {
        return (int) round($ratio * 100);
    }

    /**
     * Phòng ban còn nhân sự thì luôn có ít nhất 1 chỗ nghỉ, tránh khoá cứng phòng ban nhỏ
     * (ví dụ 3 người × 30% = 0 chỗ).
     */
    public static function slotsFor(int $headcount, float $ratio): int
    {
        if ($headcount <= 0) {
            return 0;
        }

        return max(1, (int) floor($headcount * $ratio));
    }
}
