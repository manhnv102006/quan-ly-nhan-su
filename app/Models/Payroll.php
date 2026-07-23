<?php

namespace App\Models;

use App\Services\InsuranceService;
use App\Services\TaxService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'employee_id',
        'payroll_period_id',
        'generated_by',
        'basic_salary',
        'allowance',
        'allowance_meal',
        'allowance_phone',
        'allowance_fuel',
        'allowance_position',
        'bonus',
        'overtime_hours',
        'overtime_pay',
        'standard_working_days',
        'actual_working_days',
        'deduction',
        'paid_leave_days',
        'unpaid_leave_days',
        'total_salary',
        'status',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
    ];


    protected $casts = [
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'allowance_meal' => 'decimal:2',
        'allowance_phone' => 'decimal:2',
        'allowance_fuel' => 'decimal:2',
        'allowance_position' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function salaryAdvanceDeductions(): HasMany
    {
        return $this->hasMany(SalaryAdvanceDeduction::class);
    }

    public function payrollAllowances(): HasMany
    {
        return $this->hasMany(PayrollAllowance::class);
    }

    public function payrollTaxSnapshot(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PayrollTaxSnapshot::class);
    }

    /**
     * Danh sách phụ cấp đã "chốt" (snapshot) của kỳ lương.
     * Ưu tiên bản snapshot; nếu chưa có (dữ liệu cũ) thì suy ra từ các cột phụ cấp legacy.
     *
     * @return \Illuminate\Support\Collection<int, array{label: string, code: ?string, amount: float}>
     */
    public function allowanceBreakdown(): \Illuminate\Support\Collection
    {
        $snapshots = $this->relationLoaded('payrollAllowances')
            ? $this->payrollAllowances
            : $this->payrollAllowances()->get();

        if ($snapshots->isNotEmpty()) {
            return $snapshots
                ->map(fn (PayrollAllowance $item) => [
                    'label' => $item->name,
                    'code' => $item->code,
                    'amount' => (float) $item->amount,
                ])
                ->filter(fn (array $row) => $row['amount'] > 0)
                ->values();
        }

        $legacy = [
            'Phụ cấp cố định' => (float) $this->allowance,
            'Ăn trưa' => (float) ($this->allowance_meal ?? 0),
            'Điện thoại' => (float) ($this->allowance_phone ?? 0),
            'Xăng xe' => (float) ($this->allowance_fuel ?? 0),
            'Chức vụ' => (float) ($this->allowance_position ?? 0),
        ];

        return collect($legacy)
            ->map(fn (float $amount, string $label) => [
                'label' => $label,
                'code' => null,
                'amount' => $amount,
            ])
            ->filter(fn (array $row) => $row['amount'] > 0)
            ->values();
    }

    /**
     * Tổng phụ cấp thực nhận của kỳ lương (cộng tất cả các khoản phụ cấp).
     */
    public function totalAllowance(): float
    {
        return (float) $this->allowanceBreakdown()->sum('amount');
    }

    public function advanceDeductionAmount(): float
    {
        if ($this->relationLoaded('salaryAdvanceDeductions')) {
            return (float) $this->salaryAdvanceDeductions->sum('amount');
        }

        return (float) $this->salaryAdvanceDeductions()->sum('amount');
    }

    public function outstandingAdvanceBalance(): float
    {
        if (! $this->employee_id) {
            return 0.0;
        }

        return (float) SalaryAdvance::query()
            ->where('employee_id', $this->employee_id)
            ->whereIn('status', [SalaryAdvance::STATUS_APPROVED, SalaryAdvance::STATUS_PARTIAL])
            ->get()
            ->sum(fn (SalaryAdvance $advance) => $advance->remainingBalance());
    }

    public function displayStatus(): string
    {
        return $this->status ?? $this->payrollPeriod?->status ?? 'open';
    }

    public function statusLabel(): string
    {
        return match ($this->displayStatus()) {
            'open' => 'Đang mở',
            'calculated' => 'Đã tính lương',
            'approved' => 'Đã duyệt',
            'paid' => 'Đã thanh toán',
            'closed' => 'Đã đóng',
            'draft' => 'Nháp',
            'pending' => 'Chờ duyệt',
            default => 'Chưa xác định',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->displayStatus()) {
            'paid', 'closed' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'approved' => 'bg-sky-50 text-sky-700 border-sky-100',
            'calculated', 'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
            default => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }

    /**
     * @return array{
     *     gross_income: float,
     *     penalty: float,
     *     insurance: float,
     *     bhxh_employee: float,
     *     bhyt_employee: float,
     *     bhtn_employee: float,
     *     pit: float,
     *     total_deductions: float,
     *     net_salary: float,
     *     advance_deduction: float,
     *     advance_outstanding: float,
     * }
     */
    public function payslipBreakdown(?Carbon $onDate = null): array
    {
        $advanceDeduction = $this->advanceDeductionAmount();
        $advanceOutstanding = $this->outstandingAdvanceBalance();
        $penalty = (float) $this->deduction;
        $gross = (float) $this->total_salary;
        $grossIncome = $gross + $penalty;
        $employee = $this->employee;

        if (! $employee) {
            return [
                'gross_income' => $grossIncome,
                'penalty' => $penalty,
                'insurance' => 0,
                'bhxh_employee' => 0,
                'bhyt_employee' => 0,
                'bhtn_employee' => 0,
                'pit' => 0,
                'total_deductions' => $penalty,
                'net_salary' => $gross,
                'advance_deduction' => $advanceDeduction,
                'advance_outstanding' => 0.0,
            ];
        }

        $period = $this->payrollPeriod;
        $periodDate = $onDate ?? ($period
            ? Carbon::create((int) $period->year, (int) $period->month, 15)
            : now());

        $taxService = app(TaxService::class);
        $fromSnapshot = $taxService->breakdownFromSnapshot($this);

        if ($fromSnapshot !== null) {
            $pit = (float) $fromSnapshot['pit'];
            $insurance = (float) $fromSnapshot['insurance'];
            $totalDeductions = $penalty + $insurance + $pit;
            $netSalary = max(0, $gross - $insurance - $pit);

            $bhxh = $bhyt = $bhtn = 0.0;
            $profile = $employee->insurance;
            if ($profile?->isContributing()) {
                $contributions = app(InsuranceService::class)->calculateContributions($profile);
                $bhxh = (float) $contributions['bhxh_employee'];
                $bhyt = (float) $contributions['bhyt_employee'];
                $bhtn = (float) $contributions['bhtn_employee'];
            }

            return [
                'gross_income' => $grossIncome,
                'penalty' => $penalty,
                'insurance' => $insurance,
                'bhxh_employee' => $bhxh,
                'bhyt_employee' => $bhyt,
                'bhtn_employee' => $bhtn,
                'pit' => $pit,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'advance_deduction' => $advanceDeduction,
                'advance_outstanding' => $advanceOutstanding,
            ];
        }

        $tax = $taxService->calculateEmployeeMonthly($employee, $gross, $periodDate);

        $bhxh = $bhyt = $bhtn = 0.0;
        $profile = $employee->insurance;
        if ($profile?->isContributing()) {
            $contributions = app(InsuranceService::class)->calculateContributions($profile);
            $bhxh = (float) $contributions['bhxh_employee'];
            $bhyt = (float) $contributions['bhyt_employee'];
            $bhtn = (float) $contributions['bhtn_employee'];
        }

        $insurance = (float) $tax['insurance'];
        $pit = (float) $tax['pit'];
        $totalDeductions = $penalty + $insurance + $pit;

        return [
            'gross_income' => $grossIncome,
            'penalty' => $penalty,
            'insurance' => $insurance,
            'bhxh_employee' => $bhxh,
            'bhyt_employee' => $bhyt,
            'bhtn_employee' => $bhtn,
            'pit' => $pit,
            'total_deductions' => $totalDeductions,
            'net_salary' => (float) $tax['net_income'],
            'advance_deduction' => $advanceDeduction,
            'advance_outstanding' => $advanceOutstanding,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toModalPayload(int $lateDays, string $pdfUrl): array
    {
        $breakdown = $this->payslipBreakdown();
        $fmt = fn (float $n) => number_format($n, 0, ',', '.');
        $netSalary = $breakdown['net_salary'];
        $isPaid = in_array($this->status, ['paid', 'closed']);

        return [
            'id' => $this->id,
            'employee_code' => $this->employee?->employee_code ?: '—',
            'full_name' => $this->employee?->full_name ?: '—',
            'department_name' => $this->employee?->department?->department_name ?: '—',
            'position_name' => $this->employee?->position?->position_name ?: '—',
            'period_name' => $this->payrollPeriod?->name ?: '—',
            'period_range' => ($this->payrollPeriod?->start_date?->format('d/m/Y') ?: '').' - '.($this->payrollPeriod?->end_date?->format('d/m/Y') ?: ''),
            'basic_salary' => $fmt((float) $this->basic_salary),
            'allowance' => $fmt((float) $this->allowance),
            'allowance_meal' => $fmt((float) $this->allowance_meal),
            'allowance_phone' => $fmt((float) $this->allowance_phone),
            'allowance_fuel' => $fmt((float) $this->allowance_fuel),
            'allowance_position' => $fmt((float) $this->allowance_position),
            'allowance_total' => $fmt($this->totalAllowance()),
            'allowances' => $this->allowanceBreakdown()
                ->map(fn (array $row) => [
                    'label' => $row['label'],
                    'amount' => $fmt($row['amount']),
                ])
                ->values()
                ->all(),
            'bonus' => $fmt((float) $this->bonus),
            'overtime_hours' => (float) $this->overtime_hours,
            'overtime_pay' => $fmt((float) $this->overtime_pay),
            'deduction' => $fmt((float) $this->deduction),
            'late_days' => $lateDays,
            'late_fine' => $fmt($lateDays * 50000),
            'unpaid_leave_fine' => $fmt($this->unpaid_leave_days * 300000),
            'standard_working_days' => $this->standard_working_days,
            'actual_working_days' => $this->actual_working_days,
            'gross_income' => $fmt($breakdown['gross_income']),
            'insurance_total' => $fmt($breakdown['insurance']),
            'bhxh_employee' => $fmt($breakdown['bhxh_employee']),
            'bhyt_employee' => $fmt($breakdown['bhyt_employee']),
            'bhtn_employee' => $fmt($breakdown['bhtn_employee']),
            'pit' => $fmt($breakdown['pit']),
            'advance_deduction' => $fmt($breakdown['advance_deduction']),
            'advance_outstanding' => $fmt($breakdown['advance_outstanding']),
            'total_deductions' => $fmt($breakdown['total_deductions']),
            'net_salary' => $fmt($netSalary),
            'paid_salary' => $isPaid ? $fmt($netSalary) : '0',
            'remaining_salary' => $isPaid ? '0' : $fmt($netSalary),
            'status_label' => match ($this->status) {
                'calculated' => 'Đã tính lương',
                'approved' => 'Đã duyệt',
                'paid' => 'Đã chi trả',
                'closed' => 'Đã đóng',
                default => 'Chưa tính lương',
            },
            'paid_leave_days' => $this->paid_leave_days,
            'unpaid_leave_days' => $this->unpaid_leave_days,
            'pdf_url' => $pdfUrl,
        ];
    }
}
