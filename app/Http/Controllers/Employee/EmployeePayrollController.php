<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
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

    public function index(Request $request): View
    {
        $employee = $this->getEmployee();

        $filterYear   = $request->input('year');
        $filterMonth  = $request->input('month');
        $filterStatus = $request->input('status');

        $payrollQuery = Payroll::query()
            ->where('payrolls.employee_id', $employee->id)
            ->with(['payrollPeriod.approver', 'payrollPeriod.payer'])
            ->join('payroll_periods', 'payroll_periods.id', '=', 'payrolls.payroll_period_id')
            ->select('payrolls.*');

        if ($filterYear) {
            $payrollQuery->where('payroll_periods.year', $filterYear);
        }
        if ($filterMonth) {
            $payrollQuery->where('payroll_periods.month', $filterMonth);
        }
        if ($filterStatus) {
            $payrollQuery->where('payroll_periods.status', $filterStatus);
        }

        $payrolls = $payrollQuery
            ->orderByDesc('payroll_periods.year')
            ->orderByDesc('payroll_periods.month')
            ->paginate(10)
            ->withQueryString();

        $allPayrolls = Payroll::query()
            ->where('employee_id', $employee->id)
            ->with('payrollPeriod')
            ->get();

        $payrollYears = Payroll::query()
            ->where('payrolls.employee_id', $employee->id)
            ->join('payroll_periods', 'payroll_periods.id', '=', 'payrolls.payroll_period_id')
            ->orderByDesc('payroll_periods.year')
            ->distinct()
            ->pluck('payroll_periods.year');

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

        return view('employee.payrolls.index', compact(
            'employee', 'payrolls', 'summary',
            'payrollYears', 'filterYear', 'filterMonth', 'filterStatus'
        ));
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
