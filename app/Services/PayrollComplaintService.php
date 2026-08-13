<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollComplaint;
use App\Models\PayrollPeriod;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PayrollComplaintService
{
    public function generateCode(): string
    {
        $prefix = 'KN'.now()->format('ym');
        $latest = PayrollComplaint::query()
            ->where('complaint_code', 'like', $prefix.'%')
            ->orderByDesc('complaint_code')
            ->value('complaint_code');

        $sequence = $latest ? ((int) Str::after($latest, $prefix)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function filteredQuery(?string $status = null, ?string $search = null): Builder
    {
        $query = PayrollComplaint::query()
            ->with(['employee.department', 'employee.position', 'payroll.payrollPeriod'])
            ->latest('id');

        if ($status) {
            if ($status === PayrollComplaint::STATUS_PROCESSING) {
                $query->whereIn('status', [
                    PayrollComplaint::STATUS_PENDING,
                    PayrollComplaint::STATUS_PROCESSING,
                ]);
            } else {
                $query->where('status', $status);
            }
        }

        $search = trim((string) $search);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('complaint_code', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($employee) use ($search) {
                        $employee->where('full_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    /**
     * @return array{awaiting: int, resolved: int, rejected: int}
     */
    public function dashboardStats(): array
    {
        return [
            'awaiting' => PayrollComplaint::whereIn('status', [
                PayrollComplaint::STATUS_PENDING,
                PayrollComplaint::STATUS_PROCESSING,
            ])->count(),
            'resolved' => PayrollComplaint::where('status', PayrollComplaint::STATUS_RESOLVED)->count(),
            'rejected' => PayrollComplaint::where('status', PayrollComplaint::STATUS_REJECTED)->count(),
        ];
    }

    public function loadDetail(PayrollComplaint $complaint): PayrollComplaint
    {
        return $complaint->load([
            'employee.department',
            'employee.position',
            'payroll.payrollPeriod',
            'carriedToPayroll.payrollPeriod',
            'managerConfirmer',
            'resolver.employee',
            'rejecter.employee',
        ]);
    }

    public function assertEmployeeOwnsPayroll(Employee $employee, Payroll $payroll): void
    {
        if ((int) $payroll->employee_id !== (int) $employee->id) {
            abort(403, 'Bạn không thể khiếu nại phiếu lương này.');
        }
    }

    public function assertNoOpenComplaint(Employee $employee, Payroll $payroll): void
    {
        $exists = PayrollComplaint::query()
            ->where('employee_id', $employee->id)
            ->where('payroll_id', $payroll->id)
            ->whereIn('status', [PayrollComplaint::STATUS_PENDING, PayrollComplaint::STATUS_PROCESSING])
            ->exists();

        if ($exists) {
            abort(422, 'Phiếu lương này đang có khiếu nại chưa xử lý xong.');
        }
    }

    /**
     * Kỳ lương ngay trước kỳ đích (tháng trước).
     */
    public function previousPeriod(PayrollPeriod $targetPeriod): array
    {
        $date = Carbon::create((int) $targetPeriod->year, (int) $targetPeriod->month, 1)->subMonth();

        return ['year' => (int) $date->year, 'month' => (int) $date->month];
    }

    /**
     * Kỳ lương ngay sau kỳ khiếu nại (tháng sau — nơi bổ sung tiền).
     */
    public function nextPeriodAfter(PayrollPeriod $complaintPeriod): array
    {
        $date = Carbon::create((int) $complaintPeriod->year, (int) $complaintPeriod->month, 1)->addMonth();

        return ['year' => (int) $date->year, 'month' => (int) $date->month];
    }

    /**
     * Các khiếu nại đã xử lý, chưa chuyển tiền, áp dụng cho kỳ lương đích.
     *
     * @return Collection<int, PayrollComplaint>
     */
    public function pendingCarryForwardForPeriod(Employee $employee, PayrollPeriod $targetPeriod): Collection
    {
        $previous = $this->previousPeriod($targetPeriod);

        return PayrollComplaint::query()
            ->with(['payroll.payrollPeriod'])
            ->where('employee_id', $employee->id)
            ->where('status', PayrollComplaint::STATUS_RESOLVED)
            ->whereNull('carried_to_payroll_id')
            ->where('confirmed_adjustment_amount', '>', 0)
            ->whereHas('payroll.payrollPeriod', function ($query) use ($previous) {
                $query->where('year', $previous['year'])
                    ->where('month', $previous['month']);
            })
            ->get();
    }

    /**
     * @return array{amount: float, complaints: Collection<int, PayrollComplaint>}
     */
    public function carryForwardSummary(Employee $employee, PayrollPeriod $targetPeriod): array
    {
        $complaints = $this->pendingCarryForwardForPeriod($employee, $targetPeriod);

        return [
            'amount' => (float) $complaints->sum('confirmed_adjustment_amount'),
            'complaints' => $complaints,
        ];
    }

    /**
     * @param  Collection<int, PayrollComplaint>  $complaints
     */
    public function markCarriedToPayroll(Collection $complaints, Payroll $payroll): void
    {
        if ($complaints->isEmpty()) {
            return;
        }

        PayrollComplaint::query()
            ->whereIn('id', $complaints->pluck('id'))
            ->update([
                'carried_to_payroll_id' => $payroll->id,
                'carried_at' => now(),
            ]);
    }

    public function resolveWithAdjustment(
        PayrollComplaint $complaint,
        float $adjustmentAmount,
        string $resolutionNote,
        int $resolvedBy,
    ): void {
        $complaint->update([
            'status' => PayrollComplaint::STATUS_RESOLVED,
            'confirmed_adjustment_amount' => max(0, round($adjustmentAmount, 0)),
            'resolution_note' => $resolutionNote,
            'resolved_by' => $resolvedBy,
            'resolved_at' => now(),
        ]);
    }

    /**
     * @param  array{
     *     employee_id: int,
     *     payroll_id: int,
     *     issue_type: string,
     *     subject: string,
     *     description: string,
     *     disputed_amount?: int|null,
     * }  $data
     */
    public function createComplaint(array $data): PayrollComplaint
    {
        return PayrollComplaint::create([
            'complaint_code' => $this->generateCode(),
            'employee_id' => $data['employee_id'],
            'payroll_id' => $data['payroll_id'],
            'issue_type' => $data['issue_type'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'disputed_amount' => $data['disputed_amount'] ?? null,
            'status' => PayrollComplaint::STATUS_PROCESSING,
        ]);
    }
}
