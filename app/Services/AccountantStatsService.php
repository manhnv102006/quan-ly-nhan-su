<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\SalaryAdvance;
use App\Models\TaxDependent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccountantStatsService
{
    public function __construct(
        protected ContractAllowanceService $allowanceService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboardStats(int $year, int $month, int $quarter, string $costView): array
    {
        $currentPeriod = PayrollPeriod::query()
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        $latestPeriod = PayrollPeriod::query()
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        $activePeriod = $currentPeriod ?? $latestPeriod;
        $payrollStats = $this->payrollCompanyStats($activePeriod);
        $monthlyTrend = $this->monthlyCostTrend(6);
        $quarterlySummary = $this->quarterlyCostSummary($year, $quarter);
        $expiring = $this->expiringContractsSummary();

        return [
            'year' => $year,
            'month' => $month,
            'quarter' => $quarter,
            'costView' => $costView,
            'currentPeriod' => $activePeriod,
            'selectedPeriodExists' => (bool) $currentPeriod,
            'payrollStats' => $payrollStats,
            'monthlyTrend' => $monthlyTrend,
            'quarterlySummary' => $quarterlySummary,
            'expiring' => $expiring,
            'pendingPayrollPeriods' => PayrollPeriod::query()
                ->whereIn('status', ['open', 'calculated'])
                ->count(),
            'activeEmployees' => Employee::query()->where('status', 'active')->count(),
            'departmentCount' => Department::query()->where('status', 'active')->count(),
            'pendingAdvances' => SalaryAdvance::query()->where('status', SalaryAdvance::STATUS_PENDING)->count(),
            'pendingNpt' => TaxDependent::query()->where('status', TaxDependent::STATUS_PENDING)->count(),
            'recentPeriods' => PayrollPeriod::query()
                ->withSum('payrolls', 'total_salary')
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * @return array<string, float|int|null>
     */
    public function payrollCompanyStats(?PayrollPeriod $period): array
    {
        if (! $period) {
            return [
                'employee_count' => 0,
                'basic_salary' => 0.0,
                'allowance_total' => 0.0,
                'overtime_pay' => 0.0,
                'bonus' => 0.0,
                'deduction' => 0.0,
                'gross_income' => 0.0,
                'net_payroll' => 0.0,
                'avg_net' => 0.0,
            ];
        }

        $row = DB::table('payrolls')
            ->where('payroll_period_id', $period->id)
            ->whereNull('deleted_at')
            ->selectRaw('COUNT(*) as employee_count,
                COALESCE(SUM(basic_salary), 0) as basic_salary,
                COALESCE(SUM(COALESCE(allowance, 0) + COALESCE(allowance_meal, 0) + COALESCE(allowance_phone, 0) + COALESCE(allowance_fuel, 0) + COALESCE(allowance_position, 0)), 0) as allowance_total,
                COALESCE(SUM(overtime_pay), 0) as overtime_pay,
                COALESCE(SUM(bonus), 0) as bonus,
                COALESCE(SUM(deduction), 0) as deduction,
                COALESCE(SUM(total_salary), 0) as net_payroll')
            ->first();

        $basic = (float) ($row->basic_salary ?? 0);
        $allowance = (float) ($row->allowance_total ?? 0);
        $overtime = (float) ($row->overtime_pay ?? 0);
        $bonus = (float) ($row->bonus ?? 0);
        $count = (int) ($row->employee_count ?? 0);
        $net = (float) ($row->net_payroll ?? 0);

        return [
            'employee_count' => $count,
            'basic_salary' => $basic,
            'allowance_total' => $allowance,
            'overtime_pay' => $overtime,
            'bonus' => $bonus,
            'deduction' => (float) ($row->deduction ?? 0),
            'gross_income' => $basic + $allowance + $overtime + $bonus,
            'net_payroll' => $net,
            'avg_net' => $count > 0 ? round($net / $count, 0) : 0.0,
        ];
    }

    /**
     * @return Collection<int, array{label: string, year: int, month: int, total: float, period: ?PayrollPeriod, status: ?string}>
     */
    public function monthlyCostTrend(int $limit = 6): Collection
    {
        return PayrollPeriod::query()
            ->withSum('payrolls', 'total_salary')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (PayrollPeriod $period) => [
                'label' => str_pad((string) $period->month, 2, '0', STR_PAD_LEFT).'/'.$period->year,
                'year' => (int) $period->year,
                'month' => (int) $period->month,
                'total' => (float) ($period->payrolls_sum_total_salary ?? 0),
                'period' => $period,
                'status' => $period->status,
            ]);
    }

    /**
     * @return array{label: string, year: int, quarter: int, total: float, months: Collection<int, array{label: string, total: float, period: ?PayrollPeriod}>, avg_monthly: float}
     */
    public function quarterlyCostSummary(int $year, int $quarter): array
    {
        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $startMonth + 2;

        $periods = PayrollPeriod::query()
            ->where('year', $year)
            ->whereBetween('month', [$startMonth, $endMonth])
            ->withSum('payrolls', 'total_salary')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $months = collect(range($startMonth, $endMonth))->map(function (int $m) use ($periods, $year) {
            $period = $periods->get($m);

            return [
                'label' => 'T'.$m.'/'.$year,
                'total' => (float) ($period?->payrolls_sum_total_salary ?? 0),
                'period' => $period,
            ];
        });

        $total = (float) $months->sum('total');
        $monthsWithData = $months->filter(fn ($m) => $m['total'] > 0)->count();

        return [
            'label' => 'Quý '.$quarter.'/'.$year,
            'year' => $year,
            'quarter' => $quarter,
            'total' => $total,
            'months' => $months,
            'avg_monthly' => $monthsWithData > 0 ? round($total / $monthsWithData, 0) : 0.0,
        ];
    }

    /**
     * @return array{stats: array<string, int>, upcoming: Collection<int, array{contract: Contract, days_left: int, total_income: float, urgency: string}>}
     */
    public function expiringContractsSummary(): array
    {
        $stats = [
            'within_7' => $this->expiringQuery(7)->count(),
            'within_15' => $this->expiringQuery(15)->count(),
            'within_30' => $this->expiringQuery(30)->count(),
            'within_60' => $this->expiringQuery(60)->count(),
        ];

        $upcoming = $this->expiringQuery(30)
            ->with(['employee.department', 'contractType'])
            ->orderBy('end_date')
            ->limit(6)
            ->get()
            ->map(function (Contract $contract) {
                $daysLeft = (int) now()->startOfDay()->diffInDays($contract->end_date, false);
                $allowance = $this->allowanceService->totalAllowance($contract);

                return [
                    'contract' => $contract,
                    'days_left' => $daysLeft,
                    'total_income' => (float) $contract->salary + $allowance,
                    'allowance' => $allowance,
                    'urgency' => $daysLeft <= 7 ? 'critical' : ($daysLeft <= 15 ? 'warning' : 'normal'),
                ];
            });

        return compact('stats', 'upcoming');
    }

    /**
     * @return Collection<int, array{department: Department, employee_count: int, total_salary: float, share: float}>
     */
    public function departmentPayrollBreakdown(array $salaryReport): Collection
    {
        $grandTotal = max(1, (float) ($salaryReport['totals']['total_salary'] ?? 0));

        return collect($salaryReport['rows'] ?? [])
            ->filter(fn ($row) => ($row['total_salary'] ?? 0) > 0)
            ->sortByDesc('total_salary')
            ->values()
            ->map(fn ($row) => [
                'department' => $row['department'],
                'employee_count' => $row['employee_count'],
                'total_salary' => $row['total_salary'],
                'share' => round(($row['total_salary'] / $grandTotal) * 100, 1),
            ]);
    }

    public function periodStatusLabel(?string $status): string
    {
        return match ($status) {
            'open' => 'Đang mở',
            'calculated' => 'Đã tính lương',
            'approved' => 'Đã duyệt',
            'paid' => 'Đã chi trả',
            'closed' => 'Đã khóa',
            default => $status ?? '—',
        };
    }

    public function periodStatusBadge(?string $status): string
    {
        return match ($status) {
            'open' => 'bg-sky-100 text-sky-800',
            'calculated' => 'bg-amber-100 text-amber-800',
            'approved' => 'bg-indigo-100 text-indigo-800',
            'paid' => 'bg-emerald-100 text-emerald-800',
            'closed' => 'bg-slate-100 text-slate-600',
            default => 'bg-slate-100 text-slate-600',
        };
    }

    protected function expiringQuery(int $withinDays): \Illuminate\Database\Eloquent\Builder
    {
        return Contract::query()
            ->where('status', Contract::STATUS_ACTIVE)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>', now())
            ->whereDate('end_date', '<=', now()->addDays($withinDays));
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
