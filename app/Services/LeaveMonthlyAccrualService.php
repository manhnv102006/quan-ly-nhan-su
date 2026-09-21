<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveAccrual;
use App\Models\LeaveAccrualRun;
use App\Support\LeaveAccrualRules;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveMonthlyAccrualService
{
    public function __construct(
        private readonly LeaveBalanceService $leaveBalanceService,
    ) {}

    /**
     * @return array{
     *     run: LeaveAccrualRun,
     *     created: int,
     *     skipped: int,
     *     ineligible: int,
     *     blocked_unpaid: int,
     *     blocked_sick: int
     * }
     */
    public function accrueForMonth(int $year, int $month): array
    {
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
        $daysPerMonth = (float) config('leave.accrual_days_per_month', 1);
        $now = now();

        $run = LeaveAccrualRun::query()->create([
            'accrual_year' => $year,
            'accrual_month' => $month,
            'status' => LeaveAccrualRun::STATUS_RUNNING,
            'started_at' => $now,
        ]);

        $created = 0;
        $skipped = 0;
        $ineligible = 0;
        $blockedUnpaid = 0;
        $blockedSick = 0;

        try {
            Employee::query()
                ->whereIn('status', [Employee::STATUS_ACTIVE, Employee::STATUS_ON_LEAVE])
                ->whereDate('hire_date', '<=', $monthEnd->toDateString())
                ->orderBy('id')
                ->chunkById(100, function ($employees) use (
                    $year,
                    $month,
                    $daysPerMonth,
                    $run,
                    &$created,
                    &$skipped,
                    &$ineligible,
                    &$blockedUnpaid,
                    &$blockedSick,
                ) {
                    foreach ($employees as $employee) {
                        if (! $this->employeeEligibleForAccrualMonth($employee, $year, $month)) {
                            $ineligible++;

                            continue;
                        }

                        $onMaternity = $this->leaveBalanceService->maternityLeaveCoversMonth($employee, $year, $month);

                        if (! $onMaternity && $this->leaveBalanceService->unpaidLeaveBlocksAccrualForMonth($employee, $year, $month)) {
                            $blockedUnpaid++;
                            $unpaidDays = $this->leaveBalanceService->unpaidWorkingDaysThroughMonth($employee, $year, $month);

                            Log::info('[leave:accrue-monthly] Không cộng phép do nghỉ không lương vượt ngưỡng', [
                                'employee_id' => $employee->id,
                                'employee_code' => $employee->employee_code,
                                'accrual_year' => $year,
                                'accrual_month' => $month,
                                'unpaid_working_days_ytd' => $unpaidDays,
                                'threshold' => config('leave.unpaid_leave_accrual_block_days', 12),
                            ]);

                            continue;
                        }

                        if (! $onMaternity && $this->leaveBalanceService->sickLeaveBlocksAccrualForMonth($employee, $year, $month)) {
                            $blockedSick++;
                            $sickMonths = $this->leaveBalanceService->sickLeaveMonthsInYear($employee, $year);

                            Log::info('[leave:accrue-monthly] Không cộng phép do nghỉ ốm BHXH vượt hạn tháng', [
                                'employee_id' => $employee->id,
                                'employee_code' => $employee->employee_code,
                                'accrual_year' => $year,
                                'accrual_month' => $month,
                                'sick_months_in_year' => $sickMonths,
                                'allowed_sick_months' => config('leave.sick_leave_accrual_allowed_months', 2),
                            ]);

                            continue;
                        }

                        if ($this->createAccrualIfMissing($employee, $year, $month, $daysPerMonth, $run->id)) {
                            $created++;
                        } else {
                            $skipped++;
                        }
                    }
                });

            $message = sprintf(
                'Cộng phép tháng %02d/%d: tạo mới %d, bỏ qua trùng %d, không đủ điều kiện %d, chặn nghỉ không lương %d, chặn nghỉ ốm dài %d.',
                $month,
                $year,
                $created,
                $skipped,
                $ineligible,
                $blockedUnpaid,
                $blockedSick,
            );

            $run->update([
                'status' => LeaveAccrualRun::STATUS_COMPLETED,
                'accruals_created' => $created,
                'accruals_skipped' => $skipped,
                'employees_ineligible' => $ineligible,
                'message' => $message,
                'finished_at' => now(),
            ]);

            Log::info('[leave:accrue-monthly] '.$message, [
                'run_id' => $run->id,
                'accrual_year' => $year,
                'accrual_month' => $month,
                'created' => $created,
                'skipped' => $skipped,
                'ineligible' => $ineligible,
                'blocked_unpaid' => $blockedUnpaid,
                'blocked_sick' => $blockedSick,
            ]);

            return [
                'run' => $run,
                'created' => $created,
                'skipped' => $skipped,
                'ineligible' => $ineligible,
                'blocked_unpaid' => $blockedUnpaid,
                'blocked_sick' => $blockedSick,
            ];
        } catch (\Throwable $exception) {
            $run->update([
                'status' => LeaveAccrualRun::STATUS_FAILED,
                'message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            Log::error('[leave:accrue-monthly] Lỗi cộng phép tháng '.$month.'/'.$year, [
                'run_id' => $run->id,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function employeeEligibleForAccrualMonth(Employee $employee, int $year, int $month): bool
    {
        if (! $employee->hire_date) {
            return false;
        }

        $hireDate = Carbon::parse($employee->hire_date)->startOfDay();

        return LeaveAccrualRules::employeeEligibleForAccrualMonth($hireDate, $year, $month);
    }

    protected function createAccrualIfMissing(
        Employee $employee,
        int $year,
        int $month,
        float $days,
        int $runId,
    ): bool {
        $existing = LeaveAccrual::query()
            ->where('employee_id', $employee->id)
            ->where('accrual_year', $year)
            ->where('accrual_month', $month)
            ->exists();

        if ($existing) {
            Log::debug('[leave:accrue-monthly] Bỏ qua cộng trùng', [
                'employee_id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'accrual_year' => $year,
                'accrual_month' => $month,
            ]);

            return false;
        }

        try {
            DB::table('leave_accruals')->insert([
                'employee_id' => $employee->id,
                'accrual_year' => $year,
                'accrual_month' => $month,
                'days' => $days,
                'source' => LeaveAccrual::SOURCE_SCHEDULED,
                'run_id' => $runId,
                'accrued_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (QueryException $exception) {
            if ($this->isUniqueConstraintViolation($exception)) {
                Log::debug('[leave:accrue-monthly] Bỏ qua cộng trùng (race)', [
                    'employee_id' => $employee->id,
                    'accrual_year' => $year,
                    'accrual_month' => $month,
                ]);

                return false;
            }

            throw $exception;
        }

        Log::info('[leave:accrue-monthly] Đã cộng phép', [
            'employee_id' => $employee->id,
            'employee_code' => $employee->employee_code,
            'accrual_year' => $year,
            'accrual_month' => $month,
            'days' => $days,
            'run_id' => $runId,
        ]);

        return true;
    }

    protected function isUniqueConstraintViolation(QueryException $exception): bool
    {
        $code = (string) $exception->getCode();

        return str_contains($exception->getMessage(), 'leave_accruals_employee_period_unique')
            || $code === '23000'
            || $code === '19';
    }
}
