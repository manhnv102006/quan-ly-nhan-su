<?php

namespace App\Support;

use Carbon\Carbon;

class LeaveAccrualRules
{
    public const AFTER_CUTOFF_CURRENT_MONTH = 'current_month';

    public const AFTER_CUTOFF_NEXT_MONTH = 'next_month';

    public static function midMonthCutoffDay(): int
    {
        $day = (int) config('leave.mid_month_cutoff_day', 15);

        return max(1, min(28, $day));
    }

    public static function midMonthAfterCutoff(): string
    {
        $rule = (string) config('leave.mid_month_after_cutoff', self::AFTER_CUTOFF_NEXT_MONTH);

        return in_array($rule, [self::AFTER_CUTOFF_CURRENT_MONTH, self::AFTER_CUTOFF_NEXT_MONTH], true)
            ? $rule
            : self::AFTER_CUTOFF_NEXT_MONTH;
    }

    /**
     * Tháng bắt đầu được cộng phép trong năm vào làm (1–12), null nếu chưa có tháng cộng.
     */
    public static function accrualStartMonth(Carbon $hireDate, int $year): ?int
    {
        if ($hireDate->year !== $year) {
            return null;
        }

        $hireMonth = $hireDate->month;

        if ($hireDate->day <= self::midMonthCutoffDay()) {
            return $hireMonth;
        }

        if (self::midMonthAfterCutoff() === self::AFTER_CUTOFF_CURRENT_MONTH) {
            return $hireMonth;
        }

        $nextMonth = $hireMonth + 1;

        return $nextMonth <= 12 ? $nextMonth : null;
    }

    public static function employeeEligibleForAccrualMonth(Carbon $hireDate, int $year, int $month): bool
    {
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        if ($hireDate->gt($monthEnd)) {
            return false;
        }

        if ($hireDate->year < $year) {
            return true;
        }

        if ($hireDate->year > $year) {
            return false;
        }

        $startMonth = self::accrualStartMonth($hireDate, $year);

        return $startMonth !== null && $month >= $startMonth;
    }

    /**
     * Số tháng đã kết thúc và đủ điều kiện cộng phép trong năm (không tính tháng hiện tại).
     * Phép chỉ được cộng sau khi nhân viên hoàn thành trọn một tháng làm việc.
     */
    public static function completedAccrualMonthsInYear(Carbon $hireDate, int $year, Carbon $asOf): int
    {
        $asOf = $asOf->copy()->startOfDay();

        if ($asOf->year < $year) {
            return 0;
        }

        $reference = $asOf->copy();
        if ($reference->year > $year) {
            $reference = Carbon::create($year, 12, 31)->startOfDay();
        }

        $lastCompleted = $reference->copy()->startOfMonth()->subDay();
        if ($lastCompleted->year < $year) {
            return 0;
        }

        if ($hireDate->year > $year) {
            return 0;
        }

        if ($hireDate->year < $year) {
            return min(12, $lastCompleted->month);
        }

        $startMonth = self::accrualStartMonth($hireDate, $year);
        if ($startMonth === null || $lastCompleted->month < $startMonth) {
            return 0;
        }

        return $lastCompleted->month - $startMonth + 1;
    }

    public static function proRataDescription(): string
    {
        $cutoff = self::midMonthCutoffDay();

        if (self::midMonthAfterCutoff() === self::AFTER_CUTOFF_NEXT_MONTH) {
            return "Vào từ ngày 1–{$cutoff} tính tháng hiện tại; sau ngày {$cutoff} bắt đầu cộng từ tháng sau.";
        }

        return 'Vào bất kỳ ngày nào trong tháng đều tính tròn tháng vào làm.';
    }
}
