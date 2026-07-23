<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeInsurance;
use App\Models\EmployeeTaxProfile;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\PayrollTaxSnapshot;
use App\Models\TaxDependent;
use App\Models\TaxPolicy;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TaxService
{
    public function policyForDate(Carbon $date): TaxPolicy
    {
        return TaxPolicy::forDate($date) ?? TaxPolicy::fallbackForDate($date);
    }

    public function personalDeduction(?EmployeeTaxProfile $profile, ?TaxPolicy $policy = null): float
    {
        if ($profile && $profile->personal_deduction !== null) {
            return (float) $profile->personal_deduction;
        }

        $policy ??= TaxPolicy::current();

        return (float) ($policy?->personal_deduction ?? EmployeeTaxProfile::DEFAULT_PERSONAL_DEDUCTION);
    }

    /**
     * Mức GT bản thân khi tính/chốt thuế theo kỳ: theo chính sách của kỳ,
     * trừ khi hồ sơ có mức tùy chỉnh (khác các mức mặc định từng áp dụng).
     */
    public function personalDeductionForPolicy(?EmployeeTaxProfile $profile, TaxPolicy $policy): float
    {
        if ($profile && $profile->personal_deduction !== null) {
            $stored = (float) $profile->personal_deduction;
            $systemDefaults = [11_000_000, 15_500_000, (float) $policy->personal_deduction];

            foreach ($systemDefaults as $default) {
                if (abs($stored - $default) < 0.01) {
                    return (float) $policy->personal_deduction;
                }
            }

            return $stored;
        }

        return (float) $policy->personal_deduction;
    }

    public function defaultDependentDeduction(?Carbon $onDate = null): float
    {
        $policy = TaxPolicy::forDate($onDate ?? now());

        return (float) ($policy?->dependent_deduction_default ?? TaxDependent::DEFAULT_MONTHLY_DEDUCTION);
    }

    public function activeDependentsCount(?Employee $employee, ?Carbon $onDate = null): int
    {
        if (! $employee) {
            return 0;
        }

        $date = $onDate ?? now();

        return TaxDependent::query()
            ->where('employee_id', $employee->id)
            ->where('status', TaxDependent::STATUS_APPROVED)
            ->where('is_active', true)
            ->where('start_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $date);
            })
            ->count();
    }

    public function dependentDeductionTotal(?Employee $employee, ?Carbon $onDate = null): float
    {
        if (! $employee) {
            return 0;
        }

        $date = $onDate ?? now();

        return (float) TaxDependent::query()
            ->where('employee_id', $employee->id)
            ->where('status', TaxDependent::STATUS_APPROVED)
            ->where('is_active', true)
            ->where('start_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $date);
            })
            ->sum('monthly_deduction');
    }

    public function insuranceEmployeeAmount(?Employee $employee, float $grossIncome): float
    {
        if (! $employee) {
            return round($grossIncome * 0.105, 0);
        }

        $profile = $employee->insurance;

        if ($profile && $profile->isContributing()) {
            return app(InsuranceService::class)->calculateContributions($profile)['total_employee'];
        }

        return round($grossIncome * 0.105, 0);
    }

    /**
     * @return array<string, float|int|TaxPolicy>
     */

    public function calculateEmployeeMonthly(
        Employee $employee,
        float $grossIncome,
        ?Carbon $onDate = null,
        ?TaxPolicy $policy = null,
    ): array {
        $date = $onDate ?? now();
        $policy = $policy ?? $this->policyForDate($date);
        $taxProfile = $employee->taxProfile;

    public function calculateEmployeeMonthly(?Employee $employee, float $grossIncome, ?Carbon $onDate = null): array
    {
        $date = $onDate ?? now();
        $taxProfile = $employee?->taxProfile;

        $insurance = $this->insuranceEmployeeAmount($employee, $grossIncome);
        $personal = $this->personalDeductionForPolicy($taxProfile, $policy);
        $dependentDeduction = $this->dependentDeductionTotal($employee, $date);
        $dependentsCount = $this->activeDependentsCount($employee, $date);

        $assessable = max(0, $grossIncome - $insurance);
        $taxable = max(0, $assessable - $personal - $dependentDeduction);
        $pit = $this->progressivePit($taxable, $policy->progressiveBrackets());

        return [
            'gross' => $grossIncome,
            'insurance' => $insurance,
            'personal_deduction' => $personal,
            'dependent_deduction' => $dependentDeduction,
            'dependents_count' => $dependentsCount,
            'assessable_income' => $assessable,
            'taxable_income' => $taxable,
            'pit' => $pit,
            'net_income' => max(0, $grossIncome - $insurance - $pit),
            'tax_policy' => $policy,
        ];
    }

    /**
     * @param  array<int, array{0: float, 1: float}>|null  $brackets
     */
    public function progressivePit(float $taxableIncome, ?array $brackets = null): float
    {
        if ($taxableIncome <= 0) {
            return 0;
        }

        if ($brackets === null) {
            $brackets = $this->policyForDate(now())->progressiveBrackets();
        }

        $tax = 0.0;
        $remaining = $taxableIncome;
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

    public function snapshotForPayroll(Payroll $payroll): PayrollTaxSnapshot
    {
        $payroll->loadMissing([
            'employee.taxProfile',
            'employee.insurance',
            'employee.taxDependents',
            'payrollPeriod',
        ]);

        $employee = $payroll->employee;
        abort_unless($employee, 422, 'Phiếu lương không có nhân viên.');

        $period = $payroll->payrollPeriod;
        $periodDate = $period
            ? Carbon::create((int) $period->year, (int) $period->month, 15)
            : now();

        $calc = $this->calculateEmployeeMonthly($employee, (float) $payroll->total_salary, $periodDate);
        /** @var TaxPolicy $policy */
        $policy = $calc['tax_policy'];

        return PayrollTaxSnapshot::query()->updateOrCreate(
            ['payroll_id' => $payroll->id],
            [
                'tax_policy_id' => $policy->id > 0 ? $policy->id : null,
                'policy_code' => $policy->code,
                'policy_label' => $policy->name,
                'dependents_count' => (int) $calc['dependents_count'],
                'personal_deduction' => $calc['personal_deduction'],
                'dependent_deduction' => $calc['dependent_deduction'],
                'gross_income' => $calc['gross'],
                'insurance_employee' => $calc['insurance'],
                'assessable_income' => $calc['assessable_income'],
                'taxable_income' => $calc['taxable_income'],
                'pit' => $calc['pit'],
                'net_income' => $calc['net_income'],
                'brackets_snapshot' => $policy->brackets,
            ]
        );
    }

    /**
     * @return array<string, float|int>|null
     */
    public function breakdownFromSnapshot(Payroll $payroll): ?array
    {
        $snapshot = $payroll->relationLoaded('payrollTaxSnapshot')
            ? $payroll->payrollTaxSnapshot
            : $payroll->payrollTaxSnapshot()->first();

        if (! $snapshot) {
            return null;
        }

        return [
            'gross_income' => (float) $snapshot->gross_income,
            'insurance' => (float) $snapshot->insurance_employee,
            'personal_deduction' => (float) $snapshot->personal_deduction,
            'dependent_deduction' => (float) $snapshot->dependent_deduction,
            'dependents_count' => (int) $snapshot->dependents_count,
            'taxable_income' => (float) $snapshot->taxable_income,
            'pit' => (float) $snapshot->pit,
            'net_income' => (float) $snapshot->net_income,
            'policy_label' => $snapshot->policy_label,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function calculateForPeriod(PayrollPeriod $period): Collection
    {
        $payrolls = Payroll::query()

            ->with([
                'payrollTaxSnapshot',
                'employee.taxProfile',
                'employee.insurance',
                'employee.taxDependents',
                'employee.department',
            ])

            ->with(['employee.taxProfile', 'employee.insurance', 'employee.taxDependents'])
            ->whereHas('employee')

            ->where('payroll_period_id', $period->id)
            ->orderByDesc('total_salary')
            ->get();

        $periodDate = Carbon::create($period->year, $period->month, 15);

        return $payrolls->map(function (Payroll $payroll) use ($periodDate) {
            $employee = $payroll->employee;


            if ($payroll->payrollTaxSnapshot && $employee) {
                return $payroll->payrollTaxSnapshot->toTaxRow($employee, $payroll);

            if (! $employee) {
                return null;

            }

            $calc = $this->calculateEmployeeMonthly($employee, (float) $payroll->total_salary, $periodDate);
            unset($calc['tax_policy']);

            return array_merge($calc, [
                'payroll' => $payroll,
                'employee' => $employee,
                'from_snapshot' => false,
            ]);
        })->filter()->values();
    }

    /**
     * @param  array{department_id?: mixed, search?: mixed, pit_filter?: mixed}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function filterPeriodRows(Collection $rows, array $filters): Collection
    {
        $departmentId = filled($filters['department_id'] ?? null) ? (int) $filters['department_id'] : null;
        $search = mb_strtolower(trim((string) ($filters['search'] ?? '')));
        $pitFilter = (string) ($filters['pit_filter'] ?? '');

        return $rows->filter(function (array $row) use ($departmentId, $search, $pitFilter) {
            $employee = $row['employee'] ?? null;
            if (! $employee instanceof Employee) {
                return false;
            }

            if ($departmentId && (int) $employee->department_id !== $departmentId) {
                return false;
            }

            if ($search !== '') {
                $needle = mb_strtolower(trim($employee->full_name.' '.$employee->employee_code));
                if (! str_contains($needle, $search)) {
                    return false;
                }
            }

            return match ($pitFilter) {
                'with_tax' => (float) $row['pit'] > 0,
                'no_tax' => (float) $row['pit'] <= 0,
                'with_dependents' => (int) ($row['dependents_count'] ?? 0) > 0,
                default => true,
            };
        })->values();
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    public function periodRange(string $type, int $year, int $month, int $quarter): array
    {
        if ($type === 'quarter') {
            $quarter = max(1, min(4, $quarter));
            $startMonth = ($quarter - 1) * 3 + 1;
            $start = Carbon::create($year, $startMonth, 1)->startOfMonth();
            $end = $start->copy()->addMonths(2)->endOfMonth();

            return [$start, $end, "Quý {$quarter}/{$year}"];
        }

        $start = Carbon::create($year, max(1, min(12, $month)), 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return [$start, $end, "Tháng {$month}/{$year}"];
    }

    /**
     * @return Collection<int, PayrollPeriod>
     */
    public function periodsInRange(Carbon $start, Carbon $end): Collection
    {
        return PayrollPeriod::query()
            ->where(function ($q) use ($start, $end) {
                $q->where('year', '>', $start->year)
                    ->orWhere(function ($q2) use ($start) {
                        $q2->where('year', $start->year)->where('month', '>=', $start->month);
                    });
            })
            ->where(function ($q) use ($start, $end) {
                $q->where('year', '<', $end->year)
                    ->orWhere(function ($q2) use ($end) {
                        $q2->where('year', $end->year)->where('month', '<=', $end->month);
                    });
            })
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    /**
     * @return array{headers: string[], rows: array<int, array<int, string|float|int>>}
     */
    public function buildDeclarationRows(Collection $periods): array
    {
        $headers = [
            'Mã NV', 'Họ tên', 'MST', 'Kỳ lương', 'Thu nhập',
            'BH NLĐ', 'GT bản thân', 'GT phụ thuộc', 'Số NPT',
            'TN tính thuế', 'Thuế TNCN', 'Thực lĩnh',
        ];

        $rows = [];
        foreach ($periods as $period) {
            foreach ($this->calculateForPeriod($period) as $row) {
                $rows[] = [
                    $row['employee']?->employee_code ?? '',
                    $row['employee']?->full_name ?? '',
                    $row['employee']?->taxProfile?->tax_code ?? '',
                    $period->name,
                    $row['gross'],
                    $row['insurance'],
                    $row['personal_deduction'],
                    $row['dependent_deduction'],
                    $row['dependents_count'],
                    $row['taxable_income'],
                    $row['pit'],
                    $row['net_income'],
                ];
            }
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Quyết toán thuế cuối năm theo nhân viên (dùng snapshot đã chốt từng tháng).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function yearSettlement(int $year): Collection
    {
        $periods = PayrollPeriod::query()
            ->where('year', $year)
            ->orderBy('month')
            ->get();

        if ($periods->isEmpty()) {
            return collect();
        }

        $byEmployee = [];

        foreach ($periods as $period) {
            foreach ($this->calculateForPeriod($period) as $row) {
                $empId = $row['employee']?->id;
                if (! $empId) {
                    continue;
                }

                if (! isset($byEmployee[$empId])) {
                    $byEmployee[$empId] = [
                        'employee' => $row['employee'],
                        'total_gross' => 0,
                        'total_insurance' => 0,
                        'total_pit_withheld' => 0,
                        'total_personal_deduction' => 0,
                        'total_dependent_deduction' => 0,
                        'months_count' => 0,
                    ];
                }

                $byEmployee[$empId]['total_gross'] += $row['gross'];
                $byEmployee[$empId]['total_insurance'] += $row['insurance'];
                $byEmployee[$empId]['total_pit_withheld'] += $row['pit'];
                $byEmployee[$empId]['total_personal_deduction'] += $row['personal_deduction'];
                $byEmployee[$empId]['total_dependent_deduction'] += $row['dependent_deduction'];
                $byEmployee[$empId]['months_count']++;
            }
        }

        $yearEndPolicy = $this->policyForDate(Carbon::create($year, 12, 31));

        return collect($byEmployee)->map(function (array $data) use ($year, $yearEndPolicy) {
            $employee = $data['employee'];

            $assessable = max(0, $data['total_gross'] - $data['total_insurance']);
            $taxableAnnual = max(0, $assessable - $data['total_personal_deduction'] - $data['total_dependent_deduction']);
            $avgMonthlyTaxable = $taxableAnnual / max(1, $data['months_count']);
            $pitLiability = $this->progressivePit($avgMonthlyTaxable, $yearEndPolicy->progressiveBrackets()) * $data['months_count'];

            $difference = $data['total_pit_withheld'] - $pitLiability;

            return [
                'employee' => $employee,
                'total_gross' => $data['total_gross'],
                'total_insurance' => $data['total_insurance'],
                'personal_annual' => $data['total_personal_deduction'],
                'dependent_annual' => $data['total_dependent_deduction'],
                'dependents_count' => $this->activeDependentsCount($employee),
                'taxable_annual' => $taxableAnnual,
                'pit_withheld' => $data['total_pit_withheld'],
                'pit_liability' => $pitLiability,
                'difference' => $difference,
                'settlement_status' => $difference > 0 ? 'refund' : ($difference < 0 ? 'pay_more' : 'balanced'),
                'months_count' => $data['months_count'],
                'year' => $year,
            ];
        })->sortBy(fn ($r) => $r['employee']?->full_name)->values();
    }

    public function toCsv(array $report): string
    {
        $csv = "\xEF\xBB\xBF";
        $csv .= implode(',', array_map(fn ($h) => '"'.str_replace('"', '""', $h).'"', $report['headers']))."\n";

        foreach ($report['rows'] as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"', $row))."\n";
        }

        return $csv;
    }
}
