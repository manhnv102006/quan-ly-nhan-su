<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\SalaryAdvance;
use App\Models\SalaryAdvanceDeduction;
use Illuminate\Support\Collection;

class AdvanceService
{
    public function approve(SalaryAdvance $advance, ?int $userId = null): void
    {
        abort_unless($advance->canBeApproved(), 422, 'Yêu cầu không ở trạng thái chờ duyệt.');

        $advance->update([
            'status' => SalaryAdvance::STATUS_APPROVED,
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now(),
        ]);
    }

    public function reject(SalaryAdvance $advance, string $reason, ?int $userId = null): void
    {
        abort_unless($advance->canBeRejected(), 422, 'Yêu cầu không ở trạng thái chờ duyệt.');

        $advance->update([
            'status' => SalaryAdvance::STATUS_REJECTED,
            'rejected_by' => $userId ?? auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * @return Collection<int, array{employee: \App\Models\Employee, total_advanced: float, total_settled: float, remaining: float, count: int}>
     */
    public function employeeBalances(): Collection
    {
        $advances = SalaryAdvance::query()
            ->with('employee.department')
            ->whereIn('status', [
                SalaryAdvance::STATUS_APPROVED,
                SalaryAdvance::STATUS_PARTIAL,
                SalaryAdvance::STATUS_SETTLED,
            ])
            ->get();

        return $advances
            ->groupBy('employee_id')
            ->map(function (Collection $group) {
                $employee = $group->first()->employee;
                $totalAdvanced = $group->whereIn('status', [
                    SalaryAdvance::STATUS_APPROVED,
                    SalaryAdvance::STATUS_PARTIAL,
                    SalaryAdvance::STATUS_SETTLED,
                ])->sum('amount');
                $totalSettled = $group->sum('amount_settled');
                $remaining = $group
                    ->filter(fn (SalaryAdvance $a) => $a->canBeDeducted())
                    ->sum(fn (SalaryAdvance $a) => $a->remainingBalance());

                return [
                    'employee' => $employee,
                    'total_advanced' => (float) $totalAdvanced,
                    'total_settled' => (float) $totalSettled,
                    'remaining' => (float) $remaining,
                    'count' => $group->count(),
                    'active_count' => $group->filter(fn (SalaryAdvance $a) => $a->canBeDeducted())->count(),
                ];
            })
            ->filter(fn ($row) => $row['remaining'] > 0 || $row['total_settled'] > 0)
            ->sortBy(fn ($row) => $row['employee']?->full_name)
            ->values();
    }

    public function applyDeduction(
        SalaryAdvance $advance,
        Payroll $payroll,
        ?float $amount = null,
        ?string $note = null,
    ): SalaryAdvanceDeduction {
        abort_unless($advance->canBeDeducted(), 422, 'Tạm ứng không thể khấu trừ.');
        abort_unless($payroll->employee_id === $advance->employee_id, 422, 'Bảng lương không khớp nhân viên.');
        abort_unless($payroll->status === 'calculated', 422, 'Chỉ trừ tạm ứng khi bảng lương chưa duyệt (calculated).');

        $remaining = $advance->remainingBalance();
        $deductAmount = $amount ?? $remaining;
        $deductAmount = min($deductAmount, $remaining);
        $deductAmount = min($deductAmount, (float) $payroll->total_salary);

        abort_if($deductAmount <= 0, 422, 'Số tiền khấu trừ không hợp lệ.');

        $deduction = SalaryAdvanceDeduction::create([
            'salary_advance_id' => $advance->id,
            'payroll_id' => $payroll->id,
            'payroll_period_id' => $payroll->payroll_period_id,
            'amount' => $deductAmount,
            'deducted_by' => auth()->id(),
            'note' => $note,
        ]);

        $payroll->deduction = (float) $payroll->deduction + $deductAmount;
        $payroll->total_salary = (float) $payroll->basic_salary
            + (float) $payroll->allowance
            + (float) $payroll->bonus
            + (float) $payroll->overtime_pay
            - (float) $payroll->deduction;
        $payroll->save();

        $newSettled = (float) $advance->amount_settled + $deductAmount;
        $newStatus = $newSettled >= (float) $advance->amount
            ? SalaryAdvance::STATUS_SETTLED
            : SalaryAdvance::STATUS_PARTIAL;

        $advance->update([
            'amount_settled' => $newSettled,
            'status' => $newStatus,
        ]);

        return $deduction;
    }

    /**
     * Trừ toàn bộ số dư tạm ứng vào bảng lương kỳ (tự động theo NV).
     *
     * @return array{applied: int, skipped: int, total_amount: float}
     */
    public function applyAllToPeriod(PayrollPeriod $period): array
    {
        $applied = 0;
        $skipped = 0;
        $totalAmount = 0.0;

        $payrolls = Payroll::query()
            ->where('payroll_period_id', $period->id)
            ->where('status', 'calculated')
            ->get()
            ->keyBy('employee_id');

        $advances = SalaryAdvance::query()
            ->whereIn('status', [SalaryAdvance::STATUS_APPROVED, SalaryAdvance::STATUS_PARTIAL])
            ->orderBy('request_date')
            ->get();

        foreach ($advances as $advance) {
            $payroll = $payrolls->get($advance->employee_id);
            if (! $payroll || ! $advance->canBeDeducted()) {
                $skipped++;

                continue;
            }

            $before = $advance->remainingBalance();
            if ($before <= 0) {
                $skipped++;

                continue;
            }

            $deduction = $this->applyDeduction($advance, $payroll, $before, 'Trừ tự động kỳ '.$period->name);
            $applied++;
            $totalAmount += (float) $deduction->amount;
        }

        return compact('applied', 'skipped', 'totalAmount');
    }

    public function stats(): array
    {
        return [
            'pending' => SalaryAdvance::where('status', SalaryAdvance::STATUS_PENDING)->count(),
            'approved' => SalaryAdvance::where('status', SalaryAdvance::STATUS_APPROVED)->count(),
            'partial' => SalaryAdvance::where('status', SalaryAdvance::STATUS_PARTIAL)->count(),
            'total_pending_amount' => (float) SalaryAdvance::where('status', SalaryAdvance::STATUS_PENDING)->sum('amount'),
            'total_outstanding' => (float) SalaryAdvance::query()
                ->whereIn('status', [SalaryAdvance::STATUS_APPROVED, SalaryAdvance::STATUS_PARTIAL])
                ->get()
                ->sum(fn (SalaryAdvance $a) => $a->remainingBalance()),
        ];
    }

    public function referenceSalary(Employee $employee): float
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

    public function maxAdvanceAmount(Employee $employee): float
    {
        $salary = $this->referenceSalary($employee);

        if ($salary <= 0) {
            return 10_000_000;
        }

        return round($salary * 0.5, 0);
    }

    /**
     * @param  array{amount: float|string, request_date: string, reason: string, note?: string|null}  $data
     */
    public function submitRequest(Employee $employee, array $data, int $requestedBy): SalaryAdvance
    {
        abort_unless($employee->status === 'active', 422, 'Chỉ nhân viên đang làm việc mới được ứng lương.');

        $hasPending = SalaryAdvance::query()
            ->where('employee_id', $employee->id)
            ->where('status', SalaryAdvance::STATUS_PENDING)
            ->exists();

        abort_if($hasPending, 422, 'Bạn đã có yêu cầu ứng lương đang chờ kế toán duyệt.');

        $amount = (float) $data['amount'];
        $maxAmount = $this->maxAdvanceAmount($employee);

        abort_if($amount < SalaryAdvance::MIN_AMOUNT, 422, 'Số tiền ứng tối thiểu '.number_format(SalaryAdvance::MIN_AMOUNT, 0, ',', '.').'₫.');
        abort_if($amount > $maxAmount, 422, 'Số tiền ứng không được vượt quá '.number_format($maxAmount, 0, ',', '.').'₫ (50% lương).');

        return SalaryAdvance::create([
            'employee_id' => $employee->id,
            'advance_code' => SalaryAdvance::generateCode(),
            'amount' => $amount,
            'request_date' => $data['request_date'],
            'reason' => $data['reason'],
            'note' => $data['note'] ?? null,
            'status' => SalaryAdvance::STATUS_PENDING,
            'requested_by' => $requestedBy,
        ]);
    }

    /**
     * @return array{pending: int, approved: int, outstanding: float, total_advanced: float}
     */
    public function employeeSummary(Employee $employee): array
    {
        $advances = SalaryAdvance::query()->where('employee_id', $employee->id)->get();

        return [
            'pending' => $advances->where('status', SalaryAdvance::STATUS_PENDING)->count(),
            'approved' => $advances->whereIn('status', [
                SalaryAdvance::STATUS_APPROVED,
                SalaryAdvance::STATUS_PARTIAL,
            ])->count(),
            'outstanding' => (float) $advances
                ->filter(fn (SalaryAdvance $a) => $a->canBeDeducted())
                ->sum(fn (SalaryAdvance $a) => $a->remainingBalance()),
            'total_advanced' => (float) $advances
                ->whereIn('status', [
                    SalaryAdvance::STATUS_APPROVED,
                    SalaryAdvance::STATUS_PARTIAL,
                    SalaryAdvance::STATUS_SETTLED,
                ])
                ->sum('amount'),
        ];
    }
}
