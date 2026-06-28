<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmployeePayrollController extends Controller
{
    private function getEmployee(): Employee
    {
        $employee = Employee::where('user_id', Auth::id())->first();

        if (! $employee) {
            abort(403, 'Tài khoản của bạn chưa được liên kết với hồ sơ nhân viên.');
        }

        return $employee;
    }

    public function index(): View
    {
        $employee = $this->getEmployee();

        $payrolls = Payroll::query()
            ->where('employee_id', $employee->id)
            ->with(['payrollPeriod.approver', 'payrollPeriod.payer'])
            ->join('payroll_periods', 'payroll_periods.id', '=', 'payrolls.payroll_period_id')
            ->select('payrolls.*')
            ->orderByDesc('payroll_periods.year')
            ->orderByDesc('payroll_periods.month')
            ->paginate(10);

        $allPayrolls = Payroll::query()
            ->where('employee_id', $employee->id)
            ->with('payrollPeriod')
            ->get();

        $summary = [
            'count' => $allPayrolls->count(),
            'paid_count' => $allPayrolls->filter(fn (Payroll $payroll) => in_array($payroll->displayStatus(), ['paid', 'closed'], true))->count(),
            'latest' => $allPayrolls
                ->sortByDesc(fn (Payroll $payroll) => sprintf('%04d%02d', $payroll->payrollPeriod?->year ?? 0, $payroll->payrollPeriod?->month ?? 0))
                ->first(),
            'total_paid' => $allPayrolls
                ->filter(fn (Payroll $payroll) => in_array($payroll->displayStatus(), ['paid', 'closed'], true))
                ->sum('total_salary'),
        ];

        return view('employee.payrolls.index', compact('employee', 'payrolls', 'summary'));
    }

    public function show(Payroll $payroll): View
    {
        $employee = $this->getEmployee();

        if ($payroll->employee_id !== $employee->id) {
            abort(403, 'Bạn không có quyền xem phiếu lương này.');
        }

        $payroll->load([
            'employee.department',
            'employee.position',
            'payrollPeriod.approver',
            'payrollPeriod.payer',
            'approver',
            'payer',
        ]);

        return view('employee.payrolls.show', compact('employee', 'payroll'));
    }

    public function exportPdf(Payroll $payroll)
    {
        $employee = $this->getEmployee();

        if ($payroll->employee_id !== $employee->id) {
            abort(403, 'Bạn không có quyền xem phiếu lương này.');
        }

        $payroll->load([
            'employee.department',
            'employee.position',
            'payrollPeriod.approver',
            'payrollPeriod.payer',
            'approver',
            'payer',
        ]);

        $pdf = Pdf::loadView('admin.payrolls.pdf', compact('payroll'));
        $filename = 'phieu_luong_' . ($payroll->employee?->employee_code ?: 'NV') . '_' . ($payroll->payrollPeriod?->month ?: 'ky') . '_' . ($payroll->payrollPeriod?->year ?: now()->year) . '.pdf';

        return $pdf->download($filename);
    }
}
