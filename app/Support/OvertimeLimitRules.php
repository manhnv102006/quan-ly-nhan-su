<?php

namespace App\Support;

/**
 * Quy định giới hạn giờ làm thêm theo Bộ luật Lao động Việt Nam 2019.
 */
final class OvertimeLimitRules
{
    public static function defaultNormalHoursPerDay(): float
    {
        return (float) config('overtime.default_normal_hours_per_day', 8);
    }

    public static function dailyOvertimeRatio(): float
    {
        return (float) config('overtime.daily_overtime_ratio', 0.5);
    }

    public static function maxTotalHoursPerDay(): float
    {
        return (float) config('overtime.max_total_hours_per_day', 12);
    }

    public static function percentOvertimeCap(float $normalHours): float
    {
        return round(max(0, $normalHours) * self::dailyOvertimeRatio(), 2);
    }

    public static function totalHoursOvertimeCap(float $normalHours): float
    {
        return max(0, round(self::maxTotalHoursPerDay() - max(0, $normalHours), 2));
    }

    public static function isTotalHoursCapBinding(float $normalHours): bool
    {
        return self::totalHoursOvertimeCap($normalHours) < self::percentOvertimeCap($normalHours);
    }

    public static function warningRatio(): float
    {
        return (float) config('overtime.warning_ratio', 0.8);
    }

    public static function maxHoursPerMonth(): float
    {
        return (float) config('overtime.max_hours_per_month', 40);
    }

    public static function monthlyWarningThreshold(): float
    {
        return round(self::maxHoursPerMonth() * self::warningRatio(), 2);
    }

    public static function yearlyWarningThreshold(): float
    {
        return round(self::maxHoursPerYear() * self::warningRatio(), 2);
    }

    public static function laborDepartmentNotificationHours(): float
    {
        return (float) config('overtime.labor_department_notification_hours', 200);
    }

    public static function maxHoursPerYear(): float
    {
        if (self::usesExtendedAnnualLimit()) {
            return (float) config('overtime.max_hours_per_year_extended', 300);
        }

        return (float) config('overtime.max_hours_per_year', 200);
    }

    public static function usesExtendedAnnualLimit(): bool
    {
        return (bool) config('overtime.extended_annual_limit', false);
    }

    /**
     * Tối đa giờ tăng ca trong ngày dựa trên giờ làm bình thường.
     */
    public static function maxOvertimeHoursForNormalDay(float $normalHours): float
    {
        return min(
            self::percentOvertimeCap($normalHours),
            self::totalHoursOvertimeCap($normalHours),
        );
    }
}
