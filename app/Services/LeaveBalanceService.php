<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveAccrual;
use App\Models\LeaveRequest;
use App\Support\LeaveAccrualRules;
use App\Support\LeaveDateRange;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LeaveBalanceService
{
    public const MONTHLY_PAID_DAYS = 1.0;

    public const ANNUAL_LEAVE_DAYS = 12.0;

    public static function configuredAnnualLeaveDays(): float
    {
        return (float) config('leave.annual_leave_days', self::ANNUAL_LEAVE_DAYS);
    }

    public static function configuredMonthlyPaidDays(): float
    {
        return (float) config('leave.monthly_paid_days', self::MONTHLY_PAID_DAYS);
    }

    /**
     * Hạn mức phép năm: ưu tiên sổ cộng phép (scheduler cuối tháng), fallback tính pro-rata nếu chưa có bản ghi.
     */
    public function annualQuotaForEmployee(Employee $employee, int $year, ?Carbon $asOf = null): float
    {
        $annualDays = self::configuredAnnualLeaveDays();

        if ($this->hasAccrualLedgerForYear($employee, $year)) {
            return min($this->accruedDaysForEmployee($employee, $year, $asOf), $annualDays);
        }

        return min($this->calculateProRataQuota($employee, $year, $asOf), $annualDays);
    }

    public function accruedDaysForEmployee(Employee $employee, int $year, ?Carbon $asOf = null): float
    {
        $asOf = ($asOf ?? now())->copy()->startOfDay();

        if ($asOf->year < $year) {
            return 0.0;
        }

        $query = LeaveAccrual::query()
            ->where('employee_id', $employee->id)
            ->where('accrual_year', $year);

        if ($asOf->year === $year) {
            $query->where('accrual_month', '<=', $asOf->month);
        }

        return (float) $query->sum('days');
    }

    public function hasAccrualLedgerForYear(Employee $employee, int $year): bool
    {
        return LeaveAccrual::query()
            ->where('employee_id', $employee->id)
            ->where('accrual_year', $year)
            ->exists();
    }

    /**
     * Pro-rata tính toán (dự phòng khi scheduler chưa chạy / chưa backfill).
     */
    public function calculateProRataQuota(Employee $employee, int $year, ?Carbon $asOf = null): float
    {
        $asOf = ($asOf ?? now())->copy()->startOfDay();
        $hireDate = $employee->hire_date
            ? Carbon::parse($employee->hire_date)->startOfDay()
            : null;

        $annualDays = self::configuredAnnualLeaveDays();

        if ($hireDate === null) {
            return $annualDays;
        }

        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();

        if ($hireDate->gt($yearEnd)) {
            return 0.0;
        }

        if ($hireDate->year < $year) {
            return $annualDays;
        }

        $reference = $asOf->copy();
        if ($reference->year > $year) {
            $reference = $yearEnd->copy()->startOfDay();
        } elseif ($reference->year < $year) {
            return 0.0;
        }

        $monthsAccrued = LeaveAccrualRules::completedAccrualMonthsInYear($hireDate, $year, $reference);

        return min((float) $monthsAccrued, $annualDays);
    }

    /**
     * Hạn mức nghỉ hưởng lương trong tháng: nhân viên mới phải hoàn thành ít nhất
     * một tháng làm việc (theo quy tắc cộng phép) mới được 1 ngày/tháng.
     */
    public function monthlyPaidQuotaForEmployee(Employee $employee, ?Carbon $asOf = null): float
    {
        $configured = self::configuredMonthlyPaidDays();
        $asOf = ($asOf ?? now())->copy()->startOfDay();

        if (! $employee->hire_date) {
            return $configured;
        }

        $hireDate = Carbon::parse($employee->hire_date)->startOfDay();

        if ($hireDate->year < $asOf->year) {
            return $configured;
        }

        if (LeaveAccrualRules::completedAccrualMonthsInYear($hireDate, (int) $asOf->year, $asOf) > 0) {
            return $configured;
        }

        return 0.0;
    }

    /**
     * @return array{
     *     month_label: string,
     *     year: int,
     *     monthly_quota: float,
     *     monthly_used: float,
     *     monthly_pending: float,
     *     monthly_remaining: float,
     *     annual_quota: float,
     *     annual_used: float,
     *     annual_pending: float,
     *     annual_remaining: float,
     *     annual_is_prorated: bool,
     *     carried_over: array,
     *     carried_over_remaining: float,
     *     current_year_remaining: float,
     *     total_annual_allowance: float
     * }
     */
    public function forEmployee(Employee $employee, ?Carbon $asOf = null): array
    {
        $asOf = ($asOf ?? now())->copy()->startOfDay();
        $monthStart = $asOf->copy()->startOfMonth();
        $monthEnd = $asOf->copy()->endOfMonth();
        $year = (int) $asOf->year;

        $monthlyUsed = $this->paidDaysInMonth($employee, $monthStart, $monthEnd, LeaveRequest::STATUS_APPROVED);
        $monthlyPending = $this->paidDaysInMonth($employee, $monthStart, $monthEnd, LeaveRequest::STATUS_PENDING);
        $annualPending = $this->annualDaysInYear($employee, $year, LeaveRequest::STATUS_PENDING);
        $availability = app(LeaveCarryOverService::class)->annualLeaveAvailability($employee, $year, $asOf);
        $annualDays = self::configuredAnnualLeaveDays();
        $monthlyQuota = $this->monthlyPaidQuotaForEmployee($employee, $asOf);
        $reserved = $this->reservePendingAnnualDays(
            $annualPending,
            $availability['current_year_remaining'],
            $availability['carried_over_remaining'],
        );

        return [
            'month_label' => $asOf->format('m/Y'),
            'year' => $year,
            'monthly_quota' => $monthlyQuota,
            'monthly_used' => $monthlyUsed,
            'monthly_pending' => $monthlyPending,
            'monthly_remaining' => max(0.0, $monthlyQuota - $monthlyUsed - $monthlyPending),
            'annual_quota' => $availability['annual_quota'],
            'annual_used' => $availability['annual_used'],
            'annual_pending' => $annualPending,
            'annual_remaining' => $reserved['total_remaining'],
            'current_year_remaining' => $reserved['current_year_remaining'],
            'carried_over' => $availability['carried_over'],
            'carried_over_remaining' => $reserved['carried_over_remaining'],
            'total_annual_allowance' => $availability['total_allowance'],
            'annual_is_prorated' => $availability['annual_quota'] < $annualDays,
        ];
    }

    /**
     * Đơn chờ duyệt giữ chỗ ngay: phép chuyển năm dùng trước, phần còn lại trừ phép năm hiện tại.
     * Từ chối hoặc hủy đơn thì đơn không còn pending nên số ngày được hoàn.
     *
     * @return array{current_year_remaining: float, carried_over_remaining: float, total_remaining: float}
     */
    private function reservePendingAnnualDays(float $pending, float $currentYearRemaining, float $carriedOverRemaining): array
    {
        $fromCarry = min($pending, max(0.0, $carriedOverRemaining));
        $fromCurrentYear = min(max(0.0, $pending - $fromCarry), max(0.0, $currentYearRemaining));

        $carriedRemaining = max(0.0, $carriedOverRemaining - $fromCarry);
        $currentRemaining = max(0.0, $currentYearRemaining - $fromCurrentYear);

        return [
            'current_year_remaining' => $currentRemaining,
            'carried_over_remaining' => $carriedRemaining,
            'total_remaining' => $currentRemaining + $carriedRemaining,
        ];
    }

    public function paidDaysInMonth(Employee $employee, Carbon $monthStart, Carbon $monthEnd, string $status): float
    {
        return $this->leaveWorkingDaysInPeriod(
            $employee,
            $monthStart,
            $monthEnd,
            LeaveRequest::monthlyPaidQuotaLeaveTypes(),
            $status,
        );
    }

    public function unpaidWorkingDaysThroughMonth(
        Employee $employee,
        int $year,
        int $month,
        string $status = LeaveRequest::STATUS_APPROVED,
    ): float {
        $periodStart = Carbon::create($year, 1, 1)->startOfDay();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth()->startOfDay();

        return $this->leaveWorkingDaysInPeriod(
            $employee,
            $periodStart,
            $periodEnd,
            ['unpaid'],
            $status,
        );
    }

    public function unpaidLeaveBlocksAccrualForMonth(Employee $employee, int $year, int $month): bool
    {
        $threshold = (float) config('leave.unpaid_leave_accrual_block_days', 12);

        if ($threshold <= 0) {
            return false;
        }

        return $this->unpaidWorkingDaysThroughMonth($employee, $year, $month) > $threshold;
    }

    /**
     * @return list<int> Tháng (1–12) trong năm có ít nhất 1 ngày nghỉ ốm (đã duyệt).
     */
    public function sickLeaveMonthsInYear(
        Employee $employee,
        int $year,
        string $status = LeaveRequest::STATUS_APPROVED,
    ): array {
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            if ($this->sickLeaveWorkingDaysInMonth($employee, $year, $month, $status) > 0) {
                $months[] = $month;
            }
        }

        return $months;
    }

    public function sickLeaveWorkingDaysInMonth(
        Employee $employee,
        int $year,
        int $month,
        string $status = LeaveRequest::STATUS_APPROVED,
    ): float {
        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth()->startOfDay();

        return $this->leaveWorkingDaysInPeriod($employee, $monthStart, $monthEnd, ['sick'], $status);
    }

    /**
     * Tháng ốm thứ 3 trở đi trong năm (vượt hạn mức BHXH) không được cộng phép.
     */
    public function sickLeaveBlocksAccrualForMonth(Employee $employee, int $year, int $month): bool
    {
        $allowedMonths = (int) config('leave.sick_leave_accrual_allowed_months', 2);

        if ($allowedMonths <= 0) {
            return false;
        }

        $sickMonths = $this->sickLeaveMonthsInYear($employee, $year);
        $index = array_search($month, $sickMonths, true);

        if ($index === false) {
            return false;
        }

        return ($index + 1) > $allowedMonths;
    }

    /** Nghỉ thai sản: vẫn cộng phép trong tháng có nghỉ thai sản đã duyệt. */
    public function maternityLeaveCoversMonth(
        Employee $employee,
        int $year,
        int $month,
        string $status = LeaveRequest::STATUS_APPROVED,
    ): bool {
        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth()->startOfDay();

        return $this->leaveWorkingDaysInPeriod(
            $employee,
            $monthStart,
            $monthEnd,
            ['maternity'],
            $status,
        ) > 0;
    }

    /**
     * @param  list<string>  $leaveTypes
     */
    public function leaveWorkingDaysInPeriod(
        Employee $employee,
        Carbon $periodStart,
        Carbon $periodEnd,
        array $leaveTypes,
        string $status,
    ): float {
        $requests = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('status', $status)
            ->whereIn('leave_type', $leaveTypes)
            ->overlappingPeriod($periodStart, $periodEnd)
            ->get();

        $holidays = Holiday::inRange($periodStart->toDateString(), $periodEnd->toDateString())->get();
        $total = 0.0;

        foreach ($requests as $request) {
            $requestStart = Carbon::parse($request->start_date)->startOfDay();
            $requestEnd = Carbon::parse($request->end_date)->startOfDay();
            $rangeStart = $requestStart->greaterThan($periodStart) ? $requestStart : $periodStart;
            $rangeEnd = $requestEnd->lessThan($periodEnd) ? $requestEnd : $periodEnd;

            if ($request->leave_type === 'half_day') {
                $day = $requestStart->copy();
                if ($day->betweenIncluded($periodStart->copy()->startOfDay(), $periodEnd->copy()->startOfDay())
                    && ! $day->isSunday()
                    && ! $this->isHoliday($day, $holidays)
                ) {
                    $total += 0.5;
                }

                continue;
            }

            foreach (LeaveDateRange::eachCalendarDay($rangeStart, $rangeEnd) as $day) {
                if ($day->isSunday() || $this->isHoliday($day, $holidays)) {
                    continue;
                }

                $total += 1;
            }
        }

        return $total;
    }

    public function annualDaysInYear(Employee $employee, int $year, string $status): float
    {
        return (float) LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->whereIn('leave_type', LeaveRequest::annualDeductingLeaveTypes())
            ->where('status', $status)
            ->whereYear('start_date', $year)
            ->sum('total_days');
    }

    /**
     * Số ngày phép năm còn có thể dùng khi tạo/duyệt đơn:
     * dư (sau đã duyệt) − đơn annual đang chờ (trừ $ignoreLeaveRequestId nếu có).
     */
    public function availableAnnualDaysForSubmission(
        Employee $employee,
        Carbon $referenceDate,
        ?int $ignoreLeaveRequestId = null,
    ): float {
        $year = (int) $referenceDate->year;
        $availability = app(LeaveCarryOverService::class)->annualLeaveAvailability($employee, $year, $referenceDate);
        $pending = $this->annualDaysInYear($employee, $year, LeaveRequest::STATUS_PENDING);

        if ($ignoreLeaveRequestId !== null) {
            $ignoredDays = LeaveRequest::query()
                ->whereKey($ignoreLeaveRequestId)
                ->where('employee_id', $employee->id)
                ->whereIn('leave_type', LeaveRequest::annualDeductingLeaveTypes())
                ->where('status', LeaveRequest::STATUS_PENDING)
                ->value('total_days');

            $pending = max(0.0, $pending - (float) ($ignoredDays ?? 0));
        }

        return max(0.0, $availability['total_remaining'] - $pending);
    }

    /**
     * @param  Collection<int, Holiday>  $holidays
     * @return list<string>
     */
    public function workingDayDatesInRange(Carbon $start, Carbon $end, Collection $holidays): array
    {
        $days = [];

        for ($date = $start->copy()->startOfDay(); $date->lte($end); $date->addDay()) {
            if ($date->isSunday() || $this->isHoliday($date, $holidays)) {
                continue;
            }

            $days[] = $date->toDateString();
        }

        return $days;
    }

    /**
     * @param  Collection<int, Holiday>  $holidays
     * @return array{
     *     blocked: bool,
     *     message: string|null,
     *     split: bool,
     *     paid_days: float,
     *     unpaid_days: float,
     *     segments: list<array{leave_type: string, start_date: string, end_date: string, total_days: float}>
     * }
     */
    public function planAnnualLeaveSubmission(
        Employee $employee,
        Carbon $start,
        Carbon $end,
        Collection $holidays,
        float $requestedDays,
    ): array {
        $available = $this->availableAnnualDaysForSubmission($employee, $start);
        $workingDays = $this->workingDayDatesInRange($start, $end, $holidays);

        if ($requestedDays <= $available) {
            return [
                'blocked' => false,
                'message' => null,
                'split' => false,
                'paid_days' => $requestedDays,
                'unpaid_days' => 0.0,
                'segments' => [[
                    'leave_type' => 'annual',
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'total_days' => $requestedDays,
                ]],
            ];
        }

        if ($available <= 0) {
            return [
                'blocked' => true,
                'message' => 'Bạn không còn số dư phép năm. Vui lòng chọn loại Nghỉ không lương hoặc rút ngắn thời gian nghỉ.',
                'split' => false,
                'paid_days' => 0.0,
                'unpaid_days' => 0.0,
                'segments' => [],
            ];
        }

        $paidDays = array_slice($workingDays, 0, (int) $available);
        $unpaidDays = array_slice($workingDays, (int) $available);

        $segments = array_merge(
            $this->groupWorkingDaysIntoLeaveSegments($paidDays, 'annual'),
            $this->groupWorkingDaysIntoLeaveSegments($unpaidDays, 'unpaid'),
        );

        $paidTotal = (float) count($paidDays);
        $unpaidTotal = (float) count($unpaidDays);

        return [
            'blocked' => false,
            'message' => null,
            'split' => true,
            'paid_days' => $paidTotal,
            'unpaid_days' => $unpaidTotal,
            'segments' => $segments,
        ];
    }

    /**
     * @param  list<string>  $dayStrings
     * @return list<array{leave_type: string, start_date: string, end_date: string, total_days: float}>
     */
    private function groupWorkingDaysIntoLeaveSegments(array $dayStrings, string $leaveType): array
    {
        if ($dayStrings === []) {
            return [];
        }

        $segments = [];
        $segmentStart = $dayStrings[0];
        $segmentEnd = $dayStrings[0];
        $segmentCount = 1;

        for ($i = 1, $count = count($dayStrings); $i < $count; $i++) {
            $previous = Carbon::parse($dayStrings[$i - 1])->startOfDay();
            $current = Carbon::parse($dayStrings[$i])->startOfDay();

            if ($previous->copy()->addDay()->isSameDay($current)) {
                $segmentEnd = $dayStrings[$i];
                $segmentCount++;

                continue;
            }

            $segments[] = [
                'leave_type' => $leaveType,
                'start_date' => $segmentStart,
                'end_date' => $segmentEnd,
                'total_days' => (float) $segmentCount,
            ];

            $segmentStart = $dayStrings[$i];
            $segmentEnd = $dayStrings[$i];
            $segmentCount = 1;
        }

        $segments[] = [
            'leave_type' => $leaveType,
            'start_date' => $segmentStart,
            'end_date' => $segmentEnd,
            'total_days' => (float) $segmentCount,
        ];

        return $segments;
    }

    /**
     * @param  Collection<int, Holiday>  $holidays
     */
    protected function isHoliday(Carbon $day, Collection $holidays): bool
    {
        return $holidays->contains(
            fn (Holiday $holiday) => $day->betweenIncluded($holiday->start_date, $holiday->end_date)
        );
    }
}
