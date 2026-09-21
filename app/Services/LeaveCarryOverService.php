<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveCarryOver;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveCarryOverService
{
    public function __construct(
        private readonly LeaveBalanceService $leaveBalanceService,
    ) {}

    public function expiryDateForTargetYear(int $targetYear): Carbon
    {
        $month = max(1, min(12, (int) config('leave.carry_over_expiry_month', 4)));

        return Carbon::create($targetYear, $month, 1)->endOfMonth()->startOfDay();
    }

    /**
     * @return array{created: int, skipped: int, total_days: float}
     */
    public function processCarryOverForSourceYear(int $sourceYear): array
    {
        if (! config('leave.carry_over_enabled', true)) {
            return ['created' => 0, 'skipped' => 0, 'total_days' => 0.0];
        }

        $targetYear = $sourceYear + 1;
        $asOfEnd = Carbon::create($sourceYear, 12, 31)->startOfDay();
        $maxDays = config('leave.carry_over_max_days');
        $created = 0;
        $skipped = 0;
        $totalDays = 0.0;

        Employee::query()
            ->whereIn('status', [Employee::STATUS_ACTIVE, Employee::STATUS_ON_LEAVE])
            ->orderBy('id')
            ->chunkById(100, function ($employees) use (
                $sourceYear,
                $targetYear,
                $asOfEnd,
                $maxDays,
                &$created,
                &$skipped,
                &$totalDays,
            ) {
                foreach ($employees as $employee) {
                    $record = $this->createCarryOverIfEligible(
                        $employee,
                        $sourceYear,
                        $targetYear,
                        $asOfEnd,
                        is_numeric($maxDays) ? (float) $maxDays : null,
                    );

                    if ($record === null) {
                        $skipped++;

                        continue;
                    }

                    if ($record->wasRecentlyCreated) {
                        $created++;
                        $totalDays += (float) $record->days;
                    } else {
                        $skipped++;
                    }
                }
            });

        Log::info('[leave:carry-over-annual] Chuyển phép năm '.$sourceYear.' → '.$targetYear, [
            'created' => $created,
            'skipped' => $skipped,
            'total_days' => $totalDays,
        ]);

        return [
            'created' => $created,
            'skipped' => $skipped,
            'total_days' => $totalDays,
        ];
    }

    public function createCarryOverIfEligible(
        Employee $employee,
        int $sourceYear,
        int $targetYear,
        Carbon $asOfEnd,
        ?float $maxDays = null,
    ): ?LeaveCarryOver {
        $existing = LeaveCarryOver::query()
            ->where('employee_id', $employee->id)
            ->where('source_year', $sourceYear)
            ->where('target_year', $targetYear)
            ->first();

        if ($existing) {
            return $existing;
        }

        $quota = $this->leaveBalanceService->annualQuotaForEmployee($employee, $sourceYear, $asOfEnd);
        $used = $this->leaveBalanceService->annualDaysInYear(
            $employee,
            $sourceYear,
            LeaveRequest::STATUS_APPROVED,
        );
        $remaining = max(0, $quota - $used);

        if ($maxDays !== null) {
            $remaining = min($remaining, $maxDays);
        }

        if ($remaining <= 0) {
            return null;
        }

        return LeaveCarryOver::query()->create([
            'employee_id' => $employee->id,
            'source_year' => $sourceYear,
            'target_year' => $targetYear,
            'days' => $remaining,
            'days_used' => 0,
            'expires_at' => $this->expiryDateForTargetYear($targetYear),
            'status' => LeaveCarryOver::STATUS_ACTIVE,
        ]);
    }

    public function activeCarryOver(
        Employee $employee,
        int $targetYear,
        ?Carbon $asOf = null,
    ): ?LeaveCarryOver {
        $asOf = ($asOf ?? now())->copy()->startOfDay();

        $record = LeaveCarryOver::query()
            ->where('employee_id', $employee->id)
            ->where('target_year', $targetYear)
            ->where('status', LeaveCarryOver::STATUS_ACTIVE)
            ->orderByDesc('source_year')
            ->first();

        if (! $record) {
            return null;
        }

        if ($asOf->gt($record->expires_at)) {
            $this->markExpired($record);

            return null;
        }

        if ($record->remainingDays() <= 0) {
            $record->update(['status' => LeaveCarryOver::STATUS_EXHAUSTED]);

            return null;
        }

        return $record;
    }

    public function carriedOverSnapshot(Employee $employee, int $targetYear, ?Carbon $asOf = null): array
    {
        $asOf = ($asOf ?? now())->copy()->startOfDay();
        $record = $this->activeCarryOver($employee, $targetYear, $asOf);

        if (! $record) {
            return [
                'active' => false,
                'source_year' => null,
                'days' => 0.0,
                'days_used' => 0.0,
                'remaining' => 0.0,
                'expires_at' => $this->expiryDateForTargetYear($targetYear)->toDateString(),
            ];
        }

        return [
            'active' => true,
            'source_year' => $record->source_year,
            'days' => (float) $record->days,
            'days_used' => (float) $record->days_used,
            'remaining' => $record->remainingDays(),
            'expires_at' => $record->expires_at->toDateString(),
        ];
    }

    /**
     * @return array{
     *     year: int,
     *     annual_quota: float,
     *     annual_used: float,
     *     current_year_remaining: float,
     *     carried_over: array,
     *     carried_over_remaining: float,
     *     total_allowance: float,
     *     total_remaining: float
     * }
     */
    public function annualLeaveAvailability(Employee $employee, int $year, ?Carbon $asOf = null): array
    {
        $asOf = ($asOf ?? now())->copy()->startOfDay();
        $annualQuota = $this->leaveBalanceService->annualQuotaForEmployee($employee, $year, $asOf);
        $annualUsed = $this->leaveBalanceService->annualDaysInYear(
            $employee,
            $year,
            LeaveRequest::STATUS_APPROVED,
        );
        $carried = $this->carriedOverSnapshot($employee, $year, $asOf);
        $carriedUsed = $carried['days_used'];
        $currentYearUsed = max(0, $annualUsed - $carriedUsed);
        $currentYearRemaining = max(0, $annualQuota - $currentYearUsed);
        $carriedRemaining = $carried['remaining'];
        $totalAllowance = $annualQuota + ($carried['active'] ? $carried['days'] : 0.0);
        $totalRemaining = $currentYearRemaining + $carriedRemaining;

        return [
            'year' => $year,
            'annual_quota' => $annualQuota,
            'annual_used' => $annualUsed,
            'current_year_remaining' => $currentYearRemaining,
            'carried_over' => $carried,
            'carried_over_remaining' => $carriedRemaining,
            'total_allowance' => $totalAllowance,
            'total_remaining' => $totalRemaining,
        ];
    }

    public function consumeForApprovedLeave(LeaveRequest $leaveRequest): void
    {
        if (! in_array($leaveRequest->leave_type, LeaveRequest::annualDeductingLeaveTypes(), true) || ! $leaveRequest->employee) {
            return;
        }

        $year = (int) ($leaveRequest->start_date?->year ?? now()->year);
        $daysToAllocate = (float) $leaveRequest->total_days;

        DB::transaction(function () use ($leaveRequest, $year, $daysToAllocate) {
            $record = LeaveCarryOver::query()
                ->where('employee_id', $leaveRequest->employee_id)
                ->where('target_year', $year)
                ->where('status', LeaveCarryOver::STATUS_ACTIVE)
                ->lockForUpdate()
                ->orderByDesc('source_year')
                ->first();

            if (! $record || $leaveRequest->start_date?->gt($record->expires_at)) {
                return;
            }

            $fromCarry = min($daysToAllocate, $record->remainingDays());

            if ($fromCarry <= 0) {
                return;
            }

            $record->days_used = (float) $record->days_used + $fromCarry;
            $record->status = $record->remainingDays() <= 0
                ? LeaveCarryOver::STATUS_EXHAUSTED
                : LeaveCarryOver::STATUS_ACTIVE;
            $record->save();

            Log::info('[leave:carry-over] Trừ phép chuyển từ năm trước', [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $leaveRequest->employee_id,
                'source_year' => $record->source_year,
                'target_year' => $year,
                'days' => $fromCarry,
            ]);
        });
    }

    protected function markExpired(LeaveCarryOver $record): void
    {
        if ($record->status === LeaveCarryOver::STATUS_ACTIVE) {
            $record->update(['status' => LeaveCarryOver::STATUS_EXPIRED]);

            Log::info('[leave:carry-over] Phép chuyển hết hạn', [
                'employee_id' => $record->employee_id,
                'source_year' => $record->source_year,
                'target_year' => $record->target_year,
                'remaining' => $record->remainingDays(),
            ]);
        }
    }
}
