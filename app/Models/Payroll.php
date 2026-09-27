<?php

namespace App\Models;

use App\Services\InsuranceService;
use App\Services\PayrollService;
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
        'complaint_adjustment',
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

    public function penaltyDetails(): HasMany
    {
        return $this->hasMany(PayrollPenaltyDetail::class)->orderBy('id');
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
                'net_salary' => max(0, $grossIncome - $penalty),
                'advance_deduction' => $advanceDeduction,
                'advance_outstanding' => 0.0,
            ];
        }

        $period = $this->payrollPeriod;
        $periodDate = $onDate ?? ($period
            ? Carbon::create((int) $period->year, (int) $period->month, 15)
            : now());

        $taxService = app(TaxService::class);
        $gross = $taxService->payrollGrossIncome($this);
        $tax = $taxService->calculateEmployeeMonthly($employee, $gross, $periodDate);

        $bhxh = $bhyt = $bhtn = 0.0;
        $profile = $employee->insurance;
        if ($profile?->isContributing()) {
            $contributions = app(InsuranceService::class)->calculateContributions($profile);
            $bhxh = (float) $contributions['bhxh_employee'];
            $bhyt = (float) $contributions['bhyt_employee'];
            $bhtn = (float) $contributions['bhtn_employee'];
        }

        $insurance = $bhxh + $bhyt + $bhtn;
        $pit = (float) $tax['pit'];
        // Thực lĩnh = tổng thu nhập − tổng giảm trừ.
        // Tổng giảm trừ gồm phạt (đã trừ sẵn trong total_salary), bảo hiểm NLĐ và thuế TNCN.
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
            'net_salary' => max(0, $grossIncome - $totalDeductions),
            'advance_deduction' => $advanceDeduction,
            'advance_outstanding' => $advanceOutstanding,
        ];
    }

    /**
     * @return array{amount: float, late_days: int, total_late_minutes: int, full_day_count: int}
     */
    public function latePenaltyBreakdown(): array
    {
        return app(PayrollService::class)->latePenaltyForPayroll($this);
    }

    /**
     * Tách tiền phạt trên phiếu.
     * Đi muộn, về sớm, quên checkout và nghỉ không phép tính lại từ chấm công cùng công thức lúc lập lương.
     * Khoản còn lại lấy nhãn từ lý do điều chỉnh lương nếu có nhật ký, không thì "Điều chỉnh khác".
     *
     * @return array{
     *     late: array{amount: float, late_days: int, total_late_minutes: int, full_day_count: int},
     *     early: array{amount: float, early_days: int, total_early_minutes: int},
     *     missing_checkout: array{amount: float, session_count: int},
     *     unpaid_leave_fine: float,
     *     other: float,
     *     other_label: string
     * }
     */
    public function penaltyDisplayBreakdown(): array
    {
        $service = app(PayrollService::class);
        $late = $service->latePenaltyForPayroll($this);
        $early = $service->earlyPenaltyForPayroll($this);
        $missing = $service->missingCheckoutPenaltyForPayroll($this);
        $unpaid = (float) $this->unpaid_leave_days * 300000;
        $advance = $this->advanceDeductionAmount();
        $stored = (float) $this->deduction;
        $other = round($stored - $late['amount'] - $early['amount'] - $missing['amount'] - $unpaid - $advance, 0);
        $adjustReason = ModuleChangeLog::query()
            ->where('module', ModuleChangeLog::MODULE_PAYROLL)
            ->where('entity_type', self::class)
            ->where('entity_id', $this->id)
            ->where('field_name', 'deduction')
            ->where('action', 'adjust')
            ->latest('id')
            ->value('note');
        $adjustReason = trim((string) $adjustReason);
        $otherLabel = $adjustReason !== '' ? $adjustReason : 'Điều chỉnh khác';

        return [
            'late' => $late,
            'early' => $early,
            'missing_checkout' => $missing,
            'unpaid_leave_fine' => $unpaid,
            'other' => $other,
            'other_label' => $otherLabel,
        ];
    }

    /**
     * Các dòng phạt in trên phiếu. Ưu tiên snapshot đã lưu lúc tính lương.
     *
     * @return list<array{type: string, label: string, amount: float, note: ?string}>
     */
    public function penaltySlipLines(): array
    {
        $details = $this->relationLoaded('penaltyDetails')
            ? $this->penaltyDetails
            : $this->penaltyDetails()->get();

        if ($details->isNotEmpty()) {
            return $details->map(fn (PayrollPenaltyDetail $row) => [
                'type' => $row->type,
                'label' => $row->label,
                'amount' => (float) $row->amount,
                'note' => $row->note,
            ])->values()->all();
        }

        return $this->legacyPenaltySlipLines();
    }

    public function manualPenaltyAmount(): float
    {
        $details = $this->relationLoaded('penaltyDetails')
            ? $this->penaltyDetails
            : null;

        if ($details) {
            return (float) $details->where('type', PayrollPenaltyDetail::TYPE_MANUAL)->sum('amount');
        }

        return (float) $this->penaltyDetails()
            ->where('type', PayrollPenaltyDetail::TYPE_MANUAL)
            ->sum('amount');
    }

    /**
     * Phiếu cũ chưa có snapshot: chốt các dòng tính lại được, phần lệch ghi "Điều chỉnh khác".
     */
    public function snapshotLegacyPenaltyDetails(): void
    {
        if ($this->penaltyDetails()->exists()) {
            return;
        }

        foreach ($this->legacyPenaltySlipLines() as $line) {
            $this->penaltyDetails()->create($line);
        }
    }

    public function replaceManualPenalty(float $amount, ?string $note): void
    {
        $this->snapshotLegacyPenaltyDetails();
        $this->penaltyDetails()->where('type', PayrollPenaltyDetail::TYPE_MANUAL)->delete();

        if ($amount > 0) {
            $this->penaltyDetails()->create([
                'type' => PayrollPenaltyDetail::TYPE_MANUAL,
                'label' => 'Phạt nhập tay',
                'amount' => round($amount, 0),
                'note' => $note !== null && trim($note) !== '' ? trim($note) : null,
            ]);
        }

        $this->deduction = round(
            (float) $this->penaltyDetails()->sum('amount') + $this->advanceDeductionAmount(),
            0
        );
    }

    /**
     * @return list<array{type: string, label: string, amount: float, note: ?string}>
     */
    private function legacyPenaltySlipLines(): array
    {
        $breakdown = $this->penaltyDisplayBreakdown();
        $lines = [];

        if ($breakdown['late']['amount'] > 0) {
            $lines[] = [
                'type' => PayrollPenaltyDetail::TYPE_LATE,
                'label' => 'Phạt đi muộn ('.$breakdown['late']['late_days'].' lần, '.$breakdown['late']['total_late_minutes'].' phút)',
                'amount' => (float) $breakdown['late']['amount'],
                'note' => null,
            ];
        }

        if ($breakdown['early']['amount'] > 0) {
            $lines[] = [
                'type' => PayrollPenaltyDetail::TYPE_EARLY,
                'label' => 'Phạt về sớm ('.$breakdown['early']['early_days'].' lần, '.$breakdown['early']['total_early_minutes'].' phút)',
                'amount' => (float) $breakdown['early']['amount'],
                'note' => null,
            ];
        }

        if ($breakdown['missing_checkout']['amount'] > 0) {
            $lines[] = [
                'type' => PayrollPenaltyDetail::TYPE_MISSING_CHECKOUT,
                'label' => 'Phạt quên checkout ('.$breakdown['missing_checkout']['session_count'].' buổi)',
                'amount' => (float) $breakdown['missing_checkout']['amount'],
                'note' => null,
            ];
        }

        if ($breakdown['unpaid_leave_fine'] > 0) {
            $lines[] = [
                'type' => PayrollPenaltyDetail::TYPE_UNPAID_LEAVE,
                'label' => 'Phạt nghỉ không phép ('.$this->unpaid_leave_days.' ngày)',
                'amount' => (float) $breakdown['unpaid_leave_fine'],
                'note' => null,
            ];
        }

        if ($breakdown['other'] != 0) {
            $lines[] = [
                'type' => PayrollPenaltyDetail::TYPE_LEGACY,
                'label' => 'Điều chỉnh khác',
                'amount' => (float) $breakdown['other'],
                'note' => null,
            ];
        }

        return $lines;
    }

    /**
     * @return array<string, mixed>
     */
    public function toModalPayload(string $pdfUrl): array
    {
        $breakdown = $this->payslipBreakdown();
        $penalties = $this->penaltyDisplayBreakdown();
        $latePenalty = $penalties['late'];
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
            'complaint_adjustment' => $fmt((float) ($this->complaint_adjustment ?? 0)),
            'overtime_hours' => (float) $this->overtime_hours,
            'overtime_pay' => $fmt((float) $this->overtime_pay),
            'deduction' => $fmt((float) $this->deduction),
            'late_days' => $latePenalty['late_days'],
            'total_late_minutes' => $latePenalty['total_late_minutes'],
            'late_fine' => $fmt($latePenalty['amount']),
            'early_days' => $penalties['early']['early_days'],
            'total_early_minutes' => $penalties['early']['total_early_minutes'],
            'early_fine' => $fmt($penalties['early']['amount']),
            'missing_checkout_sessions' => $penalties['missing_checkout']['session_count'],
            'missing_checkout_fine' => $fmt($penalties['missing_checkout']['amount']),
            'other_penalty' => $penalties['other'],
            'other_fine' => $fmt(abs($penalties['other'])),
            'other_label' => $penalties['other_label'],
            'penalties' => collect($this->penaltySlipLines())
                ->map(fn (array $row) => [
                    'label' => $row['label'],
                    'note' => $row['note'],
                    'amount' => $row['amount'],
                    'amount_formatted' => $fmt(abs($row['amount'])),
                ])
                ->values()
                ->all(),
            'unpaid_leave_fine' => $fmt($penalties['unpaid_leave_fine']),
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
