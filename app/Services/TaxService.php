<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeInsurance;
use App\Models\EmployeeTaxProfile;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\TaxDependent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TaxService
{
    public function personalDeduction(?EmployeeTaxProfile $profile): float
    {
        return (float) ($profile?->personal_deduction ?? EmployeeTaxProfile::DEFAULT_PERSONAL_DEDUCTION);
    }

    public function activeDependentsCount(Employee $employee, ?Carbon $onDate = null): int
    {
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

    public function dependentDeductionTotal(Employee $employee, ?Carbon $onDate = null): float
    {
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

    public function insuranceEmployeeAmount(Employee $employee, float $grossIncome): float
    {
        $profile = $employee->insurance;

        if ($profile && $profile->isContributing()) {
            return app(InsuranceService::class)->calculateContributions($profile)['total_employee'];
        }

        return round($grossIncome * 0.105, 0);
    }

    /**
     * @return array<string, float|int>
     */
    public function calculateEmployeeMonthly(Employee $employee, float $grossIncome, ?Carbon $onDate = null): array
    {
        $date = $onDate ?? now();
        $taxProfile = $employee->taxProfile;
        $insurance = $this->insuranceEmployeeAmount($employee, $grossIncome);
        $personal = $this->personalDeduction($taxProfile);
        $dependentDeduction = $this->dependentDeductionTotal($employee, $date);
        $dependentsCount = $this->activeDependentsCount($employee, $date);

        $assessable = max(0, $grossIncome - $insurance);
        $taxable = max(0, $assessable - $personal - $dependentDeduction);
        $pit = $this->progressivePit($taxable);

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
        ];
    }

    public function progressivePit(float $taxableIncome): float
    {
        if ($taxableIncome <= 0) {
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

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function calculateForPeriod(PayrollPeriod $period): Collection
    {
        $payrolls = Payroll::query()
            ->with(['employee.taxProfile', 'employee.insurance', 'employee.taxDependents', 'employee.department'])
            ->where('payroll_period_id', $period->id)
            ->orderByDesc('total_salary')
            ->get();

        $periodDate = Carbon::create($period->year, $period->month, 15);

        return $payrolls->map(function (Payroll $payroll) use ($periodDate) {
            $employee = $payroll->employee;
            $calc = $this->calculateEmployeeMonthly($employee, (float) $payroll->total_salary, $periodDate);

            return array_merge($calc, [
                'payroll' => $payroll,
                'employee' => $employee,
            ]);
        });
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
     * Quyết toán thuế cuối năm theo nhân viên.
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
                        'months_count' => 0,
                    ];
                }

                $byEmployee[$empId]['total_gross'] += $row['gross'];
                $byEmployee[$empId]['total_insurance'] += $row['insurance'];
                $byEmployee[$empId]['total_pit_withheld'] += $row['pit'];
                $byEmployee[$empId]['months_count']++;
            }
        }

        return collect($byEmployee)->map(function (array $data) use ($year) {
            $employee = $data['employee'];
            $months = max(1, $data['months_count']);
            $personalAnnual = $this->personalDeduction($employee->taxProfile) * 12;
            $dependentAnnual = $this->dependentDeductionTotal($employee) * 12;

            $assessable = max(0, $data['total_gross'] - $data['total_insurance']);
            $taxableAnnual = max(0, $assessable - $personalAnnual - $dependentAnnual);
            $avgMonthlyTaxable = $taxableAnnual / 12;
            $pitLiability = $this->progressivePit($avgMonthlyTaxable) * 12;

            $difference = $data['total_pit_withheld'] - $pitLiability;

            return [
                'employee' => $employee,
                'total_gross' => $data['total_gross'],
                'total_insurance' => $data['total_insurance'],
                'personal_annual' => $personalAnnual,
                'dependent_annual' => $dependentAnnual,
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
