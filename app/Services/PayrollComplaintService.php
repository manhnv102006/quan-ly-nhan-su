<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollComplaint;
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
}
