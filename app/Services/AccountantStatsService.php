<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;

class AccountantStatsService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboardStats(): array
    {
        $currentPeriod = PayrollPeriod::query()
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->first();

        $payrollQuery = Payroll::query();
        if ($currentPeriod) {
            $payrollQuery->where('payroll_period_id', $currentPeriod->id);
        }

        $totalPayrollThisMonth = (float) (clone $payrollQuery)->sum('total_salary');
        $pendingPayrollPeriods = PayrollPeriod::query()
            ->whereIn('status', ['open', 'calculated'])
            ->count();

        $activeEmployees = Employee::query()->where('status', 'active')->count();

        $todayAttendance = Attendance::query()
            ->whereDate('attendance_date', today())
            ->whereIn('status', ['present', 'late'])
            ->count();

        $expiringContracts = Contract::query()
            ->where('status', Contract::STATUS_ACTIVE)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<=', now()->addDays(30))
            ->count();

        $recentPeriods = PayrollPeriod::query()
            ->withSum('payrolls', 'total_salary')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit(6)
            ->get();

        return [
            'currentPeriod' => $currentPeriod,
            'totalPayrollThisMonth' => $totalPayrollThisMonth,
            'pendingPayrollPeriods' => $pendingPayrollPeriods,
            'activeEmployees' => $activeEmployees,
            'todayAttendance' => $todayAttendance,
            'expiringContracts' => $expiringContracts,
            'departmentCount' => Department::query()->where('status', 'active')->count(),
            'recentPeriods' => $recentPeriods,
        ];
    }

    /**
     * Ước tính bảo hiểm từ tổng lương (tỷ lệ phổ biến VN).
     *
     * @return array<string, float>
     */
    public function insuranceEstimates(float $grossSalary): array
    {
        return [
            'bhxh_employee' => round($grossSalary * 0.08, 0),
            'bhyt_employee' => round($grossSalary * 0.015, 0),
            'bhtn_employee' => round($grossSalary * 0.01, 0),
            'bhxh_employer' => round($grossSalary * 0.175, 0),
            'bhyt_employer' => round($grossSalary * 0.03, 0),
            'bhtn_employer' => round($grossSalary * 0.01, 0),
            'total_employee' => round($grossSalary * 0.105, 0),
            'total_employer' => round($grossSalary * 0.215, 0),
        ];
    }

    /**
     * Ước tính thuế TNCN đơn giản (giảm trừ 11tr, 1 người phụ thuộc).
     */
    public function estimatePit(float $taxableIncome): float
    {
        $deduction = 11_000_000 + 4_400_000;
        $income = max(0, $taxableIncome - $deduction);

        if ($income <= 0) {
            return 0;
        }

        $brackets = [
            [5_000_000, 0.05],
            [10_000_000, 0.10],
            [18_000_000, 0.15],
            [32_000_000, 0.20],
            [52_000_000, 0.25],
            [80_000_000, 0.30],
            [PHP_FLOAT_MAX, 0.35],
        ];

        $tax = 0.0;
        $remaining = $income;
        $prev = 0;

        foreach ($brackets as [$limit, $rate]) {
            $chunk = min($remaining, $limit - $prev);
            if ($chunk <= 0) {
                break;
            }
            $tax += $chunk * $rate;
            $remaining -= $chunk;
            $prev = $limit;
            if ($remaining <= 0) {
                break;
            }
        }

        return round($tax, 0);
    }
}
