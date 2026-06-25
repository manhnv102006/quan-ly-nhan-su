<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\PayrollPeriod;
use App\Services\PayrollService;
use Illuminate\Http\RedirectResponse;

use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function index(Request $request): View
    {
        $query = Payroll::query()->with(['employee', 'payrollPeriod.approver', 'payrollPeriod.payer']);

        // Tìm kiếm theo tên nhân viên
        if ($request->filled('search')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%');
            });
        }

        // Lọc theo kỳ lương
        if ($request->filled('period_id')) {
            $query->where('payroll_period_id', $request->period_id);
        }

        $payrolls = $query->latest()->paginate(10)->withQueryString();
        $periods = PayrollPeriod::orderBy('year', 'desc')->orderBy('month', 'desc')->get();

        return view('admin.payrolls.index', compact('payrolls', 'periods'));
    }

    public function exportPdf(Payroll $payroll)
    {
        $payroll->load([
            'employee.department',
            'employee.position',
            'payrollPeriod.approver',
            'payrollPeriod.payer'
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.payrolls.pdf', compact('payroll'));

        $filename = 'phieu_luong_' . ($payroll->employee?->employee_code ?: 'NV') . '_' . $payroll->payrollPeriod?->month . '_' . $payroll->payrollPeriod?->year . '.pdf';

        return $pdf->download($filename);
    }
}
