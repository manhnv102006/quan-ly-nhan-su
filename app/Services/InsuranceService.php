<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\EmployeeInsurance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InsuranceService
{
    /**
     * @return array<string, float>
     */
    public function defaultRates(): array
    {
        return [
            'bhxh_employee_rate' => 0.08,
            'bhxh_employer_rate' => 0.175,
            'bhyt_employee_rate' => 0.015,
            'bhyt_employer_rate' => 0.03,
            'bhtn_employee_rate' => 0.01,
            'bhtn_employer_rate' => 0.01,
        ];
    }

    /**
     * @return array<string, float>
     */
    public function calculateContributions(EmployeeInsurance $profile): array
    {
        $salary = (float) $profile->contribution_salary;

        $bhxhEmployee = round($salary * (float) $profile->bhxh_employee_rate, 0);
        $bhytEmployee = round($salary * (float) $profile->bhyt_employee_rate, 0);
        $bhtnEmployee = round($salary * (float) $profile->bhtn_employee_rate, 0);

        $bhxhEmployer = round($salary * (float) $profile->bhxh_employer_rate, 0);
        $bhytEmployer = round($salary * (float) $profile->bhyt_employer_rate, 0);
        $bhtnEmployer = round($salary * (float) $profile->bhtn_employer_rate, 0);

        return [
            'bhxh_employee' => $bhxhEmployee,
            'bhyt_employee' => $bhytEmployee,
            'bhtn_employee' => $bhtnEmployee,
            'bhxh_employer' => $bhxhEmployer,
            'bhyt_employer' => $bhytEmployer,
            'bhtn_employer' => $bhtnEmployer,
            'total_employee' => $bhxhEmployee + $bhytEmployee + $bhtnEmployee,
            'total_employer' => $bhxhEmployer + $bhytEmployer + $bhtnEmployer,
        ];
    }

    public function suggestContributionSalary(Employee $employee): float
    {
        $contract = Contract::query()
            ->where('employee_id', $employee->id)
            ->where('status', Contract::STATUS_ACTIVE)
            ->orderByDesc('start_date')
            ->first();

        if ($contract) {
            return (float) $contract->salary;
        }

        $payroll = $employee->payrolls()->orderByDesc('created_at')->first();

        return $payroll ? (float) $payroll->basic_salary : 0;
    }

    /**
     * Nhân viên đã nghỉ việc nhưng hồ sơ BH vẫn active.
     *
     * @return Collection<int, Employee>
     */
    public function resignedWithActiveInsurance(): Collection
    {
        return Employee::query()
            ->where('status', 'resigned')
            ->whereHas('insurance', fn ($q) => $q->where('status', EmployeeInsurance::STATUS_ACTIVE))
            ->with(['insurance', 'department'])
            ->orderBy('full_name')
            ->get();
    }

    /**
     * Hồ sơ đóng BH trong khoảng thời gian (tháng hoặc quý).
     *
     * @return Collection<int, EmployeeInsurance>
     */
    public function profilesForPeriod(Carbon $start, Carbon $end): Collection
    {
        return EmployeeInsurance::query()
            ->with(['employee.department'])
            ->where('start_date', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $start);
            })
            ->whereIn('status', [EmployeeInsurance::STATUS_ACTIVE, EmployeeInsurance::STATUS_STOPPED, EmployeeInsurance::STATUS_SUSPENDED])
            ->orderBy('employee_id')
            ->get()
            ->filter(function (EmployeeInsurance $profile) use ($start, $end) {
                if ($profile->status === EmployeeInsurance::STATUS_STOPPED && $profile->end_date && $profile->end_date->lt($start)) {
                    return false;
                }

                return true;
            })
            ->values();
    }

    public function stopForResignation(EmployeeInsurance $profile, ?string $reason = null): void
    {
        $profile->update([
            'status' => EmployeeInsurance::STATUS_STOPPED,
            'end_date' => $profile->end_date ?? now()->toDateString(),
            'stop_reason' => $reason ?? 'Nhân viên nghỉ việc',
            'managed_by' => auth()->id(),
        ]);
    }

    /**
     * @return array{headers: string[], rows: array<int, array<int, string|float>>}
     */
    public function buildReportRows(Collection $profiles): array
    {
        $headers = [
            'Mã NV', 'Họ tên', 'Phòng ban', 'Số BHXH', 'Mã BHYT',
            'Lương đóng BH', 'BHXH NLĐ', 'BHYT NLĐ', 'BHTN NLĐ', 'Tổng NLĐ',
            'BHXH DN', 'BHYT DN', 'BHTN DN', 'Tổng DN', 'Trạng thái',
        ];

        $rows = [];
        foreach ($profiles as $profile) {
            $c = $this->calculateContributions($profile);
            $rows[] = [
                $profile->employee?->employee_code ?? '',
                $profile->employee?->full_name ?? '',
                $profile->employee?->department?->department_name ?? '',
                $profile->social_insurance_number ?? '',
                $profile->health_insurance_code ?? '',
                (float) $profile->contribution_salary,
                $c['bhxh_employee'],
                $c['bhyt_employee'],
                $c['bhtn_employee'],
                $c['total_employee'],
                $c['bhxh_employer'],
                $c['bhyt_employer'],
                $c['bhtn_employer'],
                $c['total_employer'],
                $profile->statusLabel(),
            ];
        }

        return ['headers' => $headers, 'rows' => $rows];
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
