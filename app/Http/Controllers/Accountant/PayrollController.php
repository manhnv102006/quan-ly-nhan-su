<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\ContractHistory;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class PayrollController extends Controller
{
    public function index(Request $request): View
    {
        return view('accountant.payrolls.hub');
    }

    public function slips(Request $request): View
    {
        $periods = PayrollPeriod::orderByDesc('year')->orderByDesc('month')->get();

        if ($request->filled('department_id')) {
            return $this->slipsByDepartment($request, $periods);
        }

        $periodId = $request->input('period_id');

        $departments = \App\Models\Department::query()
            ->where('status', 'active')
            ->withCount('employees')
            ->orderBy('department_name')
            ->get()
            ->map(function (\App\Models\Department $department) use ($periodId) {
                $query = Payroll::query()
                    ->whereHas('employee', fn ($q) => $q->where('department_id', $department->id));

                if ($periodId) {
                    $query->where('payroll_period_id', $periodId);
                }

                $department->payrolls_count = (clone $query)->count();
                $department->total_salary = (float) (clone $query)->sum('total_salary');

                return $department;
            });

        $totals = [
            'payrolls' => $departments->sum('payrolls_count'),
            'salary' => $departments->sum('total_salary'),
        ];

        return view('accountant.payrolls.slips', compact('departments', 'periods', 'totals'));
    }

    protected function slipsByDepartment(Request $request, $periods): View
    {
        $department = \App\Models\Department::findOrFail($request->integer('department_id'));

        $query = Payroll::query()
            ->with(['employee', 'payrollPeriod'])
            ->whereHas('employee', fn ($q) => $q->where('department_id', $department->id));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('period_id')) {
            $query->where('payroll_period_id', $request->period_id);
        }

        $statsQuery = clone $query;

        $payrolls = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $stats = [
            'count' => (clone $statsQuery)->count(),
            'total_salary' => (float) (clone $statsQuery)->sum('total_salary'),
            'basic_salary' => (float) (clone $statsQuery)->sum('basic_salary'),
            'deduction' => (float) (clone $statsQuery)->sum('deduction'),
        ];

        return view('accountant.payrolls.slips-department', compact(
            'department',
            'payrolls',
            'periods',
            'stats',
        ));
    }

    public function salaryHistory(Request $request): View
    {
        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code']);

        $selectedEmployee = null;
        $contractHistories = collect();
        $payrollRecords = collect();
        $adjustmentLogs = collect();

        if ($request->filled('employee_id')) {
            $selectedEmployee = Employee::with(['department', 'position'])->find($request->employee_id);

            if ($selectedEmployee) {
                $contractHistories = ContractHistory::query()
                    ->where('employee_id', $selectedEmployee->id)
                    ->with(['performer', 'contract'])
                    ->orderByDesc('created_at')
                    ->limit(50)
                    ->get()
                    ->filter(function (ContractHistory $history) {
                        $changes = $history->changes ?? [];

                        return isset($changes['salary']) || str_contains(mb_strtolower($history->summary ?? ''), 'lương');
                    })
                    ->values();

                $payrollRecords = Payroll::query()
                    ->with('payrollPeriod')
                    ->where('employee_id', $selectedEmployee->id)
                    ->orderByDesc('created_at')
                    ->limit(24)
                    ->get();

                $adjustmentLogs = Activity::query()
                    ->where('subject_type', PayrollPeriod::class)
                    ->where('description', 'like', '%'.$selectedEmployee->full_name.'%')
                    ->where('event', 'updated')
                    ->with('causer')
                    ->orderByDesc('created_at')
                    ->limit(30)
                    ->get();
            }
        }

        return view('accountant.payrolls.salary-history', compact(
            'employees',
            'selectedEmployee',
            'contractHistories',
            'payrollRecords',
            'adjustmentLogs',
        ));
    }

    public function exportPdf(Payroll $payroll)
    {
        $payroll->load([
            'employee.department',
            'employee.position',
            'payrollPeriod.approver',
            'payrollPeriod.payer',
        ]);

        $pdf = Pdf::loadView('admin.payrolls.pdf', compact('payroll'));

        $filename = 'phieu_luong_'.($payroll->employee?->employee_code ?: 'NV').'_'.$payroll->payrollPeriod?->month.'_'.$payroll->payrollPeriod?->year.'.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Payroll $payroll): Response
    {
        $payroll->load(['employee.department', 'employee.position', 'payrollPeriod']);

        $rows = [
            ['Phiếu lương', $payroll->payrollPeriod?->name ?? ''],
            ['Mã NV', $payroll->employee?->employee_code ?? ''],
            ['Họ tên', $payroll->employee?->full_name ?? ''],
            ['Phòng ban', $payroll->employee?->department?->department_name ?? ''],
            ['Chức vụ', $payroll->employee?->position?->position_name ?? ''],
            ['Lương cơ bản', $payroll->basic_salary],
            ['Phụ cấp', $payroll->allowance],
            ['Thưởng KPI', $payroll->bonus],
            ['Tăng ca', $payroll->overtime_pay],
            ['Khấu trừ', $payroll->deduction],
            ['Thực lĩnh', $payroll->total_salary],
            ['Trạng thái', $payroll->statusLabel()],
        ];

        $csv = "\xEF\xBB\xBF";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"', $row))."\n";
        }

        $filename = 'phieu_luong_'.($payroll->employee?->employee_code ?: 'NV').'_'.$payroll->payrollPeriod?->month.'_'.$payroll->payrollPeriod?->year.'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function exportPeriodExcel(PayrollPeriod $payrollPeriod): Response
    {
        $payrolls = Payroll::query()
            ->with(['employee.department'])
            ->where('payroll_period_id', $payrollPeriod->id)
            ->orderBy('employee_id')
            ->get();

        $headers = ['Mã NV', 'Họ tên', 'Phòng ban', 'Lương CB', 'Phụ cấp', 'Thưởng', 'Tăng ca', 'Khấu trừ', 'Thực lĩnh', 'Trạng thái'];

        $csv = "\xEF\xBB\xBF";
        $csv .= implode(',', $headers)."\n";

        foreach ($payrolls as $payroll) {
            $row = [
                $payroll->employee?->employee_code ?? '',
                $payroll->employee?->full_name ?? '',
                $payroll->employee?->department?->department_name ?? '',
                $payroll->basic_salary,
                $payroll->allowance,
                $payroll->bonus,
                $payroll->overtime_pay,
                $payroll->deduction,
                $payroll->total_salary,
                $payroll->statusLabel(),
            ];
            $csv .= implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"', $row))."\n";
        }

        $filename = 'bang_luong_'.$payrollPeriod->month.'_'.$payrollPeriod->year.'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
