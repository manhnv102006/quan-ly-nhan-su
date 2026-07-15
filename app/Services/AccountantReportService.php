<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccountantReportService
{
    public function __construct(
        protected ContractAllowanceService $allowanceService,
        protected AccountantStatsService $statsService,
    ) {}

    /**
     * @return array{period: ?PayrollPeriod, rows: Collection<int, array<string, mixed>>, totals: array<string, float|int>}
     */
    public function salaryCostByDepartment(?PayrollPeriod $period): array
    {
        $departments = Department::query()
            ->where('status', 'active')
            ->orderBy('department_name')
            ->get();

        $aggregates = collect();
        if ($period) {
            $aggregates = DB::table('payrolls')
                ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
                ->where('payrolls.payroll_period_id', $period->id)
                ->whereNull('payrolls.deleted_at')
                ->selectRaw('employees.department_id,
                    COUNT(payrolls.id) as employee_count,
                    SUM(payrolls.basic_salary) as basic_salary,
                    SUM(COALESCE(payrolls.allowance, 0) + COALESCE(payrolls.allowance_meal, 0) + COALESCE(payrolls.allowance_phone, 0) + COALESCE(payrolls.allowance_fuel, 0) + COALESCE(payrolls.allowance_position, 0)) as allowance_total,
                    SUM(COALESCE(payrolls.overtime_pay, 0)) as overtime_pay,
                    SUM(COALESCE(payrolls.bonus, 0)) as bonus,
                    SUM(COALESCE(payrolls.deduction, 0)) as deduction,
                    SUM(payrolls.total_salary) as total_salary')
                ->groupBy('employees.department_id')
                ->get()
                ->keyBy('department_id');
        }

        $totals = [
            'employee_count' => 0,
            'basic_salary' => 0.0,
            'allowance_total' => 0.0,
            'overtime_pay' => 0.0,
            'bonus' => 0.0,
            'deduction' => 0.0,
            'total_salary' => 0.0,
        ];

        $rows = $departments->map(function (Department $department) use ($aggregates, &$totals) {
            $agg = $aggregates->get($department->id);

            $row = [
                'department' => $department,
                'employee_count' => (int) ($agg->employee_count ?? 0),
                'basic_salary' => (float) ($agg->basic_salary ?? 0),
                'allowance_total' => (float) ($agg->allowance_total ?? 0),
                'overtime_pay' => (float) ($agg->overtime_pay ?? 0),
                'bonus' => (float) ($agg->bonus ?? 0),
                'deduction' => (float) ($agg->deduction ?? 0),
                'total_salary' => (float) ($agg->total_salary ?? 0),
            ];

            foreach (['employee_count', 'basic_salary', 'allowance_total', 'overtime_pay', 'bonus', 'deduction', 'total_salary'] as $key) {
                $totals[$key] += $row[$key];
            }

            return $row;
        });

        return [
            'period' => $period,
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    /**
     * @return array{period: ?PayrollPeriod, rows: Collection<int, array<string, mixed>>, totals: array<string, float|int>}
     */
    public function budgetComparison(int $year, int $month): array
    {
        $period = PayrollPeriod::query()
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        $salaryReport = $this->salaryCostByDepartment($period);
        $plannedMap = $this->plannedBudgetByDepartment();

        $totals = [
            'planned' => 0.0,
            'actual' => 0.0,
            'variance' => 0.0,
            'headcount_planned' => 0,
            'headcount_actual' => 0,
        ];

        $rows = $salaryReport['rows']->map(function (array $row) use ($plannedMap, &$totals) {
            $deptId = $row['department']->id;
            $planned = $plannedMap->get($deptId, ['planned' => 0.0, 'headcount' => 0]);
            $actual = $row['total_salary'];
            $plannedAmount = (float) $planned['planned'];
            $variance = $actual - $plannedAmount;
            $variancePct = $plannedAmount > 0 ? round(($variance / $plannedAmount) * 100, 1) : ($actual > 0 ? 100.0 : 0.0);

            $totals['planned'] += $plannedAmount;
            $totals['actual'] += $actual;
            $totals['variance'] += $variance;
            $totals['headcount_planned'] += (int) $planned['headcount'];
            $totals['headcount_actual'] += $row['employee_count'];

            return [
                'department' => $row['department'],
                'headcount_planned' => (int) $planned['headcount'],
                'headcount_actual' => $row['employee_count'],
                'planned' => $plannedAmount,
                'actual' => $actual,
                'variance' => $variance,
                'variance_pct' => $variancePct,
                'status' => $variance > 0 ? 'over' : ($variance < 0 ? 'under' : 'on_track'),
            ];
        });

        // Phòng ban có ngân sách dự kiến nhưng chưa có payroll
        $existingIds = $rows->pluck('department.id')->all();
        foreach ($plannedMap as $deptId => $planned) {
            if (in_array($deptId, $existingIds, true)) {
                continue;
            }

            $department = Department::find($deptId);
            if (! $department) {
                continue;
            }

            $plannedAmount = (float) $planned['planned'];
            $totals['planned'] += $plannedAmount;
            $totals['headcount_planned'] += (int) $planned['headcount'];
            $totals['variance'] -= $plannedAmount;

            $rows->push([
                'department' => $department,
                'headcount_planned' => (int) $planned['headcount'],
                'headcount_actual' => 0,
                'planned' => $plannedAmount,
                'actual' => 0.0,
                'variance' => -$plannedAmount,
                'variance_pct' => -100.0,
                'status' => 'under',
            ]);
        }

        $rows = $rows->sortBy(fn ($r) => $r['department']->department_name)->values();

        return [
            'period' => $period,
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    /**
     * @return Collection<int, array{planned: float, headcount: int}>
     */
    public function plannedBudgetByDepartment(): Collection
    {
        $employees = Employee::query()
            ->where('status', 'active')
            ->with(['contracts' => fn ($q) => $q
                ->where('status', Contract::STATUS_ACTIVE)
                ->with('contractAllowances.allowanceType')])
            ->get(['id', 'department_id']);

        $map = collect();

        foreach ($employees as $employee) {
            $contract = $employee->contracts->sortByDesc('start_date')->first();
            if (! $contract) {
                continue;
            }

            $monthly = (float) $contract->salary + $this->allowanceService->totalAllowance($contract);
            $deptId = $employee->department_id;

            if (! $map->has($deptId)) {
                $map->put($deptId, ['planned' => 0.0, 'headcount' => 0]);
            }

            $current = $map->get($deptId);
            $current['planned'] += $monthly;
            $current['headcount']++;
            $map->put($deptId, $current);
        }

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    public function financialSummary(?PayrollPeriod $period): array
    {
        $salaryReport = $this->salaryCostByDepartment($period);
        $gross = (float) $salaryReport['totals']['total_salary'];
        $insurance = $this->statsService->insuranceEstimates($gross);
        $pit = $this->statsService->estimatePit($gross);

        return [
            'period' => $period,
            'salary' => $salaryReport,
            'gross_payroll' => $gross,
            'insurance' => $insurance,
            'estimated_pit' => $pit,
            'net_estimate' => max(0, $gross - $insurance['total_employee'] - $pit),
            'employer_cost' => $gross + $insurance['total_employer'],
        ];
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    public function financialToCsv(array $summary): string
    {
        $period = $summary['period'];
        $label = $period
            ? 'Tháng '.$period->month.'/'.$period->year
            : 'Chưa chọn kỳ';

        $lines = [];
        $lines[] = ['BÁO CÁO TÀI CHÍNH NHÂN SỰ', $label];
        $lines[] = [];
        $lines[] = ['TỔNG HỢP'];
        $lines[] = ['Tổng chi phí lương (thực tế)', $this->formatNumber($summary['gross_payroll'])];
        $lines[] = ['BH NLĐ (ước tính)', $this->formatNumber($summary['insurance']['total_employee'])];
        $lines[] = ['BH DN (ước tính)', $this->formatNumber($summary['insurance']['total_employer'])];
        $lines[] = ['Thuế TNCN (ước tính)', $this->formatNumber($summary['estimated_pit'])];
        $lines[] = ['Thực lĩnh ước tính', $this->formatNumber($summary['net_estimate'])];
        $lines[] = ['Tổng chi phí DN', $this->formatNumber($summary['employer_cost'])];
        $lines[] = [];

        $lines[] = ['CHI PHÍ LƯƠNG THEO PHÒNG BAN'];
        $lines[] = ['Phòng ban', 'Số NV', 'Lương CB', 'Phụ cấp', 'Tăng ca', 'Thưởng', 'Khấu trừ', 'Thực chi'];

        foreach ($summary['salary']['rows'] as $row) {
            $lines[] = [
                $row['department']->department_name,
                $row['employee_count'],
                $this->formatNumber($row['basic_salary']),
                $this->formatNumber($row['allowance_total']),
                $this->formatNumber($row['overtime_pay']),
                $this->formatNumber($row['bonus']),
                $this->formatNumber($row['deduction']),
                $this->formatNumber($row['total_salary']),
            ];
        }

        $lines[] = [];
        $budget = $this->budgetComparison(
            (int) ($period?->year ?? now()->year),
            (int) ($period?->month ?? now()->month),
        );

        $lines[] = ['SO SÁNH NGÂN SÁCH DỰ KIẾN VS THỰC TẾ'];
        $lines[] = ['Phòng ban', 'NV dự kiến', 'NV thực tế', 'Dự kiến (HĐ)', 'Thực tế', 'Chênh lệch', 'Tỷ lệ %'];

        foreach ($budget['rows'] as $row) {
            $lines[] = [
                $row['department']->department_name,
                $row['headcount_planned'],
                $row['headcount_actual'],
                $this->formatNumber($row['planned']),
                $this->formatNumber($row['actual']),
                $this->formatNumber($row['variance']),
                $row['variance_pct'].'%',
            ];
        }

        $csv = "\xEF\xBB\xBF";
        foreach ($lines as $line) {
            $csv .= implode(',', array_map(
                fn ($v) => '"'.str_replace('"', '""', (string) $v).'"',
                $line
            ))."\n";
        }

        return $csv;
    }

    public function salaryCostToCsv(array $report): string
    {
        $csv = "\xEF\xBB\xBF";
        $headers = ['Phòng ban', 'Mã PB', 'Số NV', 'Lương CB', 'Phụ cấp', 'Tăng ca', 'Thưởng', 'Khấu trừ', 'Thực chi'];
        $csv .= implode(',', array_map(fn ($h) => '"'.$h.'"', $headers))."\n";

        foreach ($report['rows'] as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"', [
                $row['department']->department_name,
                $row['department']->department_code,
                $row['employee_count'],
                $this->formatNumber($row['basic_salary']),
                $this->formatNumber($row['allowance_total']),
                $this->formatNumber($row['overtime_pay']),
                $this->formatNumber($row['bonus']),
                $this->formatNumber($row['deduction']),
                $this->formatNumber($row['total_salary']),
            ]))."\n";
        }

        return $csv;
    }

    public function budgetToCsv(array $report): string
    {
        $csv = "\xEF\xBB\xBF";
        $headers = ['Phòng ban', 'NV dự kiến', 'NV thực tế', 'Ngân sách dự kiến (HĐ)', 'Thực tế', 'Chênh lệch', 'Tỷ lệ %', 'Trạng thái'];
        $csv .= implode(',', array_map(fn ($h) => '"'.$h.'"', $headers))."\n";

        foreach ($report['rows'] as $row) {
            $status = match ($row['status']) {
                'over' => 'Vượt ngân sách',
                'under' => 'Dưới ngân sách',
                default => 'Đúng kế hoạch',
            };

            $csv .= implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"', [
                $row['department']->department_name,
                $row['headcount_planned'],
                $row['headcount_actual'],
                $this->formatNumber($row['planned']),
                $this->formatNumber($row['actual']),
                $this->formatNumber($row['variance']),
                $row['variance_pct'].'%',
                $status,
            ]))."\n";
        }

        return $csv;
    }

    private function formatNumber(float $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
