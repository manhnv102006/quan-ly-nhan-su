<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Support\LeaveDateRange;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LeaveBalanceService
{
    public const MONTHLY_PAID_DAYS = 1.0;

    public const ANNUAL_LEAVE_DAYS = 12.0;

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
     *     annual_remaining: float
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
        $annualUsed = $this->annualDaysInYear($employee, $year, LeaveRequest::STATUS_APPROVED);
        $annualPending = $this->annualDaysInYear($employee, $year, LeaveRequest::STATUS_PENDING);

        return [
            'month_label' => $asOf->format('m/Y'),
            'year' => $year,
            'monthly_quota' => self::MONTHLY_PAID_DAYS,
            'monthly_used' => $monthlyUsed,
            'monthly_pending' => $monthlyPending,
            'monthly_remaining' => max(0, self::MONTHLY_PAID_DAYS - $monthlyUsed),
            'annual_quota' => self::ANNUAL_LEAVE_DAYS,
            'annual_used' => $annualUsed,
            'annual_pending' => $annualPending,
            'annual_remaining' => max(0, self::ANNUAL_LEAVE_DAYS - $annualUsed),
        ];
    }

    public function paidDaysInMonth(Employee $employee, Carbon $monthStart, Carbon $monthEnd, string $status): float
    {
        $requests = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('status', $status)
            ->whereIn('leave_type', LeaveRequest::paidLeaveTypes())
            ->overlappingPeriod($monthStart, $monthEnd)
            ->get();

        $holidays = Holiday::inRange($monthStart->toDateString(), $monthEnd->toDateString())->get();
        $total = 0.0;

        foreach ($requests as $request) {
            $requestStart = Carbon::parse($request->start_date)->startOfDay();
            $requestEnd = Carbon::parse($request->end_date)->startOfDay();
            $rangeStart = $requestStart->greaterThan($monthStart) ? $requestStart : $monthStart;
            $rangeEnd = $requestEnd->lessThan($monthEnd) ? $requestEnd : $monthEnd;

            if ($request->leave_type === 'half_day') {
                $day = $requestStart->copy();
                if ($day->betweenIncluded($monthStart->copy()->startOfDay(), $monthEnd->copy()->startOfDay())
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
            ->where('leave_type', 'annual')
            ->where('status', $status)
            ->whereYear('start_date', $year)
            ->sum('total_days');
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
