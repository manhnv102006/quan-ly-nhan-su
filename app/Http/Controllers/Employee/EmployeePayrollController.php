<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
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
            ->with('payrollPeriod')
            ->latest()
            ->paginate(10);

        return view('employee.payrolls.index', compact('payrolls'));
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
        ]);

        $pdf = Pdf::loadView('admin.payrolls.pdf', compact('payroll'));
        $filename = 'phieu_luong_' . ($payroll->employee?->employee_code ?: 'NV') . '_' . $payroll->payrollPeriod?->month . '_' . $payroll->payrollPeriod?->year . '.pdf';

        return $pdf->download($filename);
    }
}
