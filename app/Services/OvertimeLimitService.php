<?php

namespace App\Services;

use App\Models\EmployeeShift;
use App\Models\OvertimeRequest;
use App\Support\OvertimeLimitMessages;
use App\Support\OvertimeLimitRules;
use App\Support\TimeInput;
use Carbon\Carbon;

/**
 * Kiểm tra giới hạn giờ làm thêm theo Bộ luật Lao động Việt Nam 2019.
 *
 * - Ngày: không quá 50% số giờ làm việc bình thường của ngày đó;
 *   nếu công ty tính theo tuần thì tổng giờ làm + tăng ca không quá 12h/ngày.
 * - Tháng: không quá 40 giờ.
 * - Năm: không quá 200 giờ (một số ngành đặc thù tối đa 300 giờ).
 */
class OvertimeLimitService
{
    /** @deprecated Dùng OvertimeLimitRules::defaultNormalHoursPerDay() */
    public const STANDARD_WORK_HOURS_PER_DAY = 8.0;

    /** @deprecated Giới hạn ngày phụ thuộc ca làm; dùng maxOvertimeHoursForDay() */
    public const MAX_HOURS_PER_DAY = 4.0;

    /** @deprecated Dùng OvertimeLimitRules::maxHoursPerMonth() */
    public const MAX_HOURS_PER_MONTH = 40.0;

    /** @deprecated Dùng OvertimeLimitRules::maxHoursPerYear() */
    public const MAX_HOURS_PER_YEAR = 200.0;

    /**
     * Số giờ giữa hai mốc thời gian HH:MM (hỗ trợ ca qua đêm).
     */
    public function hoursBetween(string $startTime, string $endTime): float
    {
        $start = Carbon::createFromFormat('H:i', TimeInput::forInput($startTime));
        $end = Carbon::createFromFormat('H:i', TimeInput::forInput($endTime));

        $minutes = $start->diffInMinutes($end, false);
        if ($minutes < 0) {
            $minutes += 24 * 60;
        }

        return round($minutes / 60, 2);
    }

    /**
     * Tổng giờ làm việc bình thường theo ca được phân trong ngày.
     */
    public function normalWorkHoursForDay(int $employeeId, Carbon $date): float
    {
        $shifts = EmployeeShift::query()
            ->where('employee_id', $employeeId)
            ->whereDate('work_date', $date->format('Y-m-d'))
            ->with('shift')
            ->get();

        $total = 0.0;

        foreach ($shifts as $employeeShift) {
            $shift = $employeeShift->shift;

            if (! $shift) {
                continue;
            }

            $total += $this->hoursBetween(
                $shift->start_time->format('H:i'),
                $shift->end_time->format('H:i'),
            );
        }

        if ($total <= 0) {
            return OvertimeLimitRules::defaultNormalHoursPerDay();
        }

        return round($total, 2);
    }

    /**
     * Giới hạn tăng ca tối đa trong ngày (50% giờ làm bình thường, có thể bị siết bởi 12h/ngày).
     */
    public function maxOvertimeHoursForDay(int $employeeId, Carbon|string $workDate): float
    {
        $date = $workDate instanceof Carbon ? $workDate : Carbon::parse($workDate);
        $normalHours = $this->normalWorkHoursForDay($employeeId, $date);

        return OvertimeLimitRules::maxOvertimeHoursForNormalDay($normalHours);
    }

    /**
     * Thông tin giới hạn hiển thị trên form / hướng dẫn nhân viên.
     *
     * @return array{
     *     normal_hours: float,
     *     max_daily_overtime: float,
     *     max_monthly_overtime: float,
     *     max_annual_overtime: float,
     *     max_total_hours_per_day: float,
     *     total_hours_cap_binding: bool,
     *     monthly_hours_used: float,
     *     remaining_monthly_hours: float,
     *     annual_hours_used: float,
     *     remaining_annual_hours: float,
     *     extended_annual_limit: bool,
     *     warnings: list<string>,
     * }
     */
    public function contextForEmployee(int $employeeId, string $workDate): array
    {
        $date = Carbon::parse($workDate);
        $monthlyHoursUsed = $this->monthlyHoursUsed($employeeId, $date);

        $context = [
            'normal_hours' => $this->normalWorkHoursForDay($employeeId, $date),
            'max_daily_overtime' => $this->maxOvertimeHoursForDay($employeeId, $date),
            'max_monthly_overtime' => OvertimeLimitRules::maxHoursPerMonth(),
            'max_annual_overtime' => OvertimeLimitRules::maxHoursPerYear(),
            'max_total_hours_per_day' => OvertimeLimitRules::maxTotalHoursPerDay(),
            'total_hours_cap_binding' => OvertimeLimitRules::isTotalHoursCapBinding(
                $this->normalWorkHoursForDay($employeeId, $date),
            ),
            'monthly_hours_used' => $monthlyHoursUsed,
            'remaining_monthly_hours' => $this->remainingMonthlyHours($employeeId, $date),
            'annual_hours_used' => $this->yearlyHoursUsed($employeeId, $date),
            'remaining_annual_hours' => $this->remainingYearlyHours($employeeId, $date),
            'extended_annual_limit' => OvertimeLimitRules::usesExtendedAnnualLimit(),
        ];

        $context['warnings'] = $this->warnings($employeeId, $workDate);

        return $context;
    }

    /**
     * Cảnh báo khi tiến gần ngưỡng (không chặn gửi đơn).
     *
     * @return list<string>
     */
    public function warnings(int $employeeId, Carbon|string $workDate, ?float $additionalHours = null): array
    {
        $date = $workDate instanceof Carbon ? $workDate : Carbon::parse($workDate);
        $monthlyUsed = $this->monthlyHoursUsed($employeeId, $date);
        $yearlyUsed = $this->yearlyHoursUsed($employeeId, $date);

        if ($additionalHours !== null) {
            $monthlyUsed = round($monthlyUsed + $additionalHours, 2);
            $yearlyUsed = round($yearlyUsed + $additionalHours, 2);
        }

        $warnings = [];
        $maxMonthly = OvertimeLimitRules::maxHoursPerMonth();
        $monthlyThreshold = OvertimeLimitRules::monthlyWarningThreshold();

        if ($monthlyUsed >= $monthlyThreshold && $monthlyUsed < $maxMonthly) {
            $warnings[] = OvertimeLimitMessages::monthlyApproaching($monthlyUsed, $maxMonthly, $monthlyThreshold);
        }

        $maxYearly = OvertimeLimitRules::maxHoursPerYear();
        $yearlyThreshold = OvertimeLimitRules::yearlyWarningThreshold();

        if ($yearlyUsed >= $yearlyThreshold && $yearlyUsed < $maxYearly) {
            $warnings[] = OvertimeLimitMessages::yearlyApproaching($yearlyUsed, $maxYearly, $yearlyThreshold);
        }

        if ($yearlyUsed >= OvertimeLimitRules::laborDepartmentNotificationHours()) {
            $warnings[] = OvertimeLimitMessages::laborDepartmentNotification($yearlyUsed);
        }

        return $warnings;
    }

    public function monthlyHoursUsed(int $employeeId, Carbon|string $workDate, ?int $ignoreId = null): float
    {
        $date = $workDate instanceof Carbon ? $workDate : Carbon::parse($workDate);

        return $this->sumHours($employeeId, $ignoreId, function ($query) use ($date) {
            $query->whereYear('work_date', $date->year)
                ->whereMonth('work_date', $date->month);
        });
    }

    public function remainingMonthlyHours(int $employeeId, Carbon|string $workDate, ?int $ignoreId = null): float
    {
        $used = $this->monthlyHoursUsed($employeeId, $workDate, $ignoreId);

        return max(0, round(OvertimeLimitRules::maxHoursPerMonth() - $used, 2));
    }

    public function yearlyHoursUsed(int $employeeId, Carbon|string $workDate, ?int $ignoreId = null): float
    {
        $date = $workDate instanceof Carbon ? $workDate : Carbon::parse($workDate);

        return $this->sumHours($employeeId, $ignoreId, function ($query) use ($date) {
            $query->whereYear('work_date', $date->year);
        });
    }

    public function remainingYearlyHours(int $employeeId, Carbon|string $workDate, ?int $ignoreId = null): float
    {
        $used = $this->yearlyHoursUsed($employeeId, $workDate, $ignoreId);

        return max(0, round(OvertimeLimitRules::maxHoursPerYear() - $used, 2));
    }

    /**
     * Trả về danh sách lỗi vi phạm giới hạn (rỗng nếu hợp lệ).
     *
     * @return array<string, string> map field => message
     */
    public function violations(int $employeeId, string $workDate, float $newHours, ?int $ignoreId = null): array
    {
        $date = Carbon::parse($workDate);
        $errors = [];

        $normalHours = $this->normalWorkHoursForDay($employeeId, $date);
        $maxDaily = $this->maxOvertimeHoursForDay($employeeId, $date);

        $dayExisting = $this->sumHours($employeeId, $ignoreId, function ($query) use ($date) {
            $query->whereDate('work_date', $date->format('Y-m-d'));
        });
        $dayTotal = round($dayExisting + $newHours, 2);

        if ($dayTotal > $maxDaily) {
            $errors['start_time'] = $this->dailyLimitMessage(
                $normalHours,
                $maxDaily,
                $dayExisting,
                $newHours,
                $dayTotal,
            );
        }

        $maxMonthly = OvertimeLimitRules::maxHoursPerMonth();
        $monthExisting = $this->monthlyHoursUsed($employeeId, $date, $ignoreId);
        $monthTotal = round($monthExisting + $newHours, 2);
        $remainingMonthly = $this->remainingMonthlyHours($employeeId, $date, $ignoreId);

        if ($monthTotal > $maxMonthly) {
            $errors['work_date'] = sprintf(
                'Vượt giới hạn tăng ca trong tháng %02d/%d: tối đa %sh/tháng. Đã có %sh, đơn này %sh → tổng %sh. Còn %s giờ tháng này.',
                $date->month,
                $date->year,
                $this->format($maxMonthly),
                $this->format($monthExisting),
                $this->format($newHours),
                $this->format($monthTotal),
                $this->format($remainingMonthly),
            );
        }

        $maxYearly = OvertimeLimitRules::maxHoursPerYear();
        $yearExisting = $this->yearlyHoursUsed($employeeId, $date, $ignoreId);
        $yearTotal = round($yearExisting + $newHours, 2);
        $remainingYearly = $this->remainingYearlyHours($employeeId, $date, $ignoreId);

        if ($yearTotal > $maxYearly) {
            $errors['work_date'] = sprintf(
                'Vượt giới hạn tăng ca trong năm %d: tối đa %sh/năm%s. Đã có %sh, đơn này %sh → tổng %sh. Còn %s giờ năm nay.',
                $date->year,
                $this->format($maxYearly),
                OvertimeLimitRules::usesExtendedAnnualLimit() ? ' (ngành đặc thù)' : '',
                $this->format($yearExisting),
                $this->format($newHours),
                $this->format($yearTotal),
                $this->format($remainingYearly),
            );
        }

        return $errors;
    }

    private function dailyLimitMessage(
        float $normalHours,
        float $maxDaily,
        float $dayExisting,
        float $newHours,
        float $dayTotal,
    ): string {
        if (OvertimeLimitRules::isTotalHoursCapBinding($normalHours)) {
            return sprintf(
                'Vượt giới hạn tăng ca trong ngày: tổng giờ làm bình thường (%sh) + tăng ca không được vượt %sh/ngày → tối đa %sh OT. Đã có %sh OT, đơn này %sh → tổng %sh OT.',
                $this->format($normalHours),
                $this->format(OvertimeLimitRules::maxTotalHoursPerDay()),
                $this->format($maxDaily),
                $this->format($dayExisting),
                $this->format($newHours),
                $this->format($dayTotal),
            );
        }

        $ratioPercent = (int) round(OvertimeLimitRules::dailyOvertimeRatio() * 100);

        return sprintf(
            'Vượt giới hạn tăng ca trong ngày: tối đa %sh/ngày (%d%% giờ làm bình thường %sh). Đã có %sh, đơn này %sh → tổng %sh.',
            $this->format($maxDaily),
            $ratioPercent,
            $this->format($normalHours),
            $this->format($dayExisting),
            $this->format($newHours),
            $this->format($dayTotal),
        );
    }

    /**
     * Tổng giờ tăng ca chiếm hạn mức theo điều kiện lọc.
     *
     * Đơn chờ duyệt (pending) được tính để tránh hai đơn cùng được duyệt rồi vượt trần.
     * Đơn từ chối (rejected) không tính.
     */
    private function sumHours(int $employeeId, ?int $ignoreId, callable $scope): float
    {
        $query = OvertimeRequest::query()
            ->where('employee_id', $employeeId)
            ->whereIn('status', [
                OvertimeRequest::STATUS_PENDING,
                OvertimeRequest::STATUS_APPROVED,
                OvertimeRequest::STATUS_COMPLETED,
            ])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId));

        $scope($query);

        return (float) $query->sum('total_hours');
    }

    private function format(float $hours): string
    {
        return rtrim(rtrim(number_format($hours, 2, '.', ''), '0'), '.');
    }
}
