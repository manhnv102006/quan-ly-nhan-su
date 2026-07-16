<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\SalaryAdvance;
use App\Services\AdvanceService;
use App\Services\ModuleChangeLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvanceController extends Controller
{
    public function __construct(
        private readonly AdvanceService $advances,
        private readonly ModuleChangeLogService $changeLogs,
    ) {}

    public function index(Request $request): View
    {
        if ($request->filled('employee_id')) {
            return $this->employeeAdvances($request->integer('employee_id'), $request);
        }

        if ($request->filled('department_id')) {
            return $this->departmentEmployees(Department::findOrFail($request->department_id), $request);
        }

        $departments = Department::query()
            ->where('status', 'active')
            ->withCount('employees')
            ->orderBy('department_name')
            ->get()
            ->map(function (Department $department) {
                $employeeIds = $department->employees()->pluck('id');

                $advances = $employeeIds->isEmpty()
                    ? collect()
                    : SalaryAdvance::query()->whereIn('employee_id', $employeeIds)->get();

                $department->advances_count = $advances->count();
                $department->pending_count = $advances->where('status', SalaryAdvance::STATUS_PENDING)->count();
                $department->outstanding_amount = (float) $advances
                    ->filter(fn (SalaryAdvance $a) => $a->canBeDeducted())
                    ->sum(fn (SalaryAdvance $a) => $a->remainingBalance());

                return $department;
            });

        return view('accountant.advances.index', [
            'departments' => $departments,
            'stats' => $this->advances->stats(),
        ]);
    }

    protected function departmentEmployees(Department $department, Request $request): View
    {
        $employeeIds = $department->employees()->pluck('id');

        $allAdvances = $employeeIds->isEmpty()
            ? collect()
            : SalaryAdvance::query()->whereIn('employee_id', $employeeIds)->get();

        $departmentStats = [
            'employees_with_advances' => $allAdvances->pluck('employee_id')->unique()->count(),
            'pending' => $allAdvances->where('status', SalaryAdvance::STATUS_PENDING)->count(),
            'outstanding' => (float) $allAdvances
                ->filter(fn (SalaryAdvance $a) => $a->canBeDeducted())
                ->sum(fn (SalaryAdvance $a) => $a->remainingBalance()),
        ];

        $query = $department->employees()
            ->with(['position', 'salaryAdvances'])
            ->whereHas('salaryAdvances')
            ->orderBy('full_name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->status;
            $query->whereHas('salaryAdvances', fn ($q) => $q->where('status', $status));
        }

        $employees = $query->get()->map(function (Employee $employee) {
            $advances = $employee->salaryAdvances->sortByDesc('request_date');
            $employee->advances_count = $advances->count();
            $employee->pending_count = $advances->where('status', SalaryAdvance::STATUS_PENDING)->count();
            $employee->outstanding_amount = (float) $advances
                ->filter(fn (SalaryAdvance $a) => $a->canBeDeducted())
                ->sum(fn (SalaryAdvance $a) => $a->remainingBalance());
            $employee->latest_advance = $advances->first();

            return $employee;
        });

        return view('accountant.advances.department-employees', [
            'department' => $department,
            'employees' => $employees,
            'departmentStats' => $departmentStats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    protected function employeeAdvances(int $employeeId, Request $request): View
    {
        $employee = Employee::with(['department', 'position'])->findOrFail($employeeId);

        $query = SalaryAdvance::query()
            ->where('employee_id', $employee->id)
            ->with(['approver', 'rejecter']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $advances = $query->orderByDesc('request_date')->orderByDesc('id')->paginate(10)->withQueryString();

        $summary = [
            'total' => SalaryAdvance::where('employee_id', $employee->id)->count(),
            'pending' => SalaryAdvance::where('employee_id', $employee->id)->where('status', SalaryAdvance::STATUS_PENDING)->count(),
            'outstanding' => (float) SalaryAdvance::where('employee_id', $employee->id)
                ->get()
                ->filter(fn (SalaryAdvance $a) => $a->canBeDeducted())
                ->sum(fn (SalaryAdvance $a) => $a->remainingBalance()),
        ];

        return view('accountant.advances.employee-advances', [
            'employee' => $employee,
            'department' => $employee->department,
            'advances' => $advances,
            'summary' => $summary,
            'filters' => $request->only(['status']),
        ]);
    }

    public function show(SalaryAdvance $advance): View
    {
        $advance->load(['employee.department', 'approver', 'rejecter', 'requester', 'deductions.payrollPeriod', 'deductions.deductor']);

        return view('accountant.advances.show', compact('advance'));
    }

    public function approve(SalaryAdvance $advance): RedirectResponse
    {
        $oldStatus = $advance->status;
        $this->advances->approve($advance);
        $this->changeLogs->logAdvanceStatusChange($advance->fresh(), 'approve', $oldStatus, SalaryAdvance::STATUS_APPROVED);

        return back()->with('success', "Đã duyệt tạm ứng {$advance->advance_code}.");
    }

    public function reject(Request $request, SalaryAdvance $advance): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $oldStatus = $advance->status;
        $this->advances->reject($advance, $validated['rejection_reason']);
        $this->changeLogs->logAdvanceStatusChange(
            $advance->fresh(),
            'reject',
            $oldStatus,
            SalaryAdvance::STATUS_REJECTED,
            $validated['rejection_reason'],
        );

        return back()->with('success', "Đã từ chối tạm ứng {$advance->advance_code}.");
    }

    public function balances(): View
    {
        $balances = $this->advances->employeeBalances();
        $stats = $this->advances->stats();

        return view('accountant.advances.balances', compact('balances', 'stats'));
    }

    public function deduct(Request $request): View
    {
        $periodId = $request->integer('period_id') ?: PayrollPeriod::query()
            ->whereIn('status', ['open', 'calculated'])
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->value('id');

        $periods = PayrollPeriod::query()
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit(18)
            ->get();

        $period = $periodId ? PayrollPeriod::find($periodId) : null;

        $rows = collect();
        if ($period) {
            $payrolls = Payroll::query()
                ->with('employee')
                ->where('payroll_period_id', $period->id)
                ->where('status', 'calculated')
                ->get()
                ->keyBy('employee_id');

            $advances = SalaryAdvance::query()
                ->with('employee')
                ->whereIn('status', [SalaryAdvance::STATUS_APPROVED, SalaryAdvance::STATUS_PARTIAL])
                ->orderBy('request_date')
                ->get()
                ->filter(fn (SalaryAdvance $a) => $a->canBeDeducted());

            foreach ($advances as $advance) {
                $rows->push([
                    'advance' => $advance,
                    'payroll' => $payrolls->get($advance->employee_id),
                    'remaining' => $advance->remainingBalance(),
                ]);
            }
        }

        return view('accountant.advances.deduct', compact('periods', 'period', 'rows'));
    }

    public function applyDeduction(Request $request, SalaryAdvance $advance): RedirectResponse
    {
        $validated = $request->validate([
            'payroll_id' => 'required|exists:payrolls,id',
            'amount' => 'nullable|numeric|min:1',
            'note' => 'nullable|string|max:500',
        ]);

        $payroll = Payroll::findOrFail($validated['payroll_id']);
        $remainingBefore = $advance->remainingBalance();

        $deduction = $this->advances->applyDeduction(
            $advance,
            $payroll,
            isset($validated['amount']) ? (float) $validated['amount'] : null,
            $validated['note'] ?? null,
        );

        $this->changeLogs->logAdvanceDeduction(
            $advance->fresh(),
            (float) $deduction->amount,
            $remainingBefore,
            $validated['note'] ?? null,
        );

        return back()->with('success', 'Đã trừ tạm ứng vào bảng lương.');
    }

    public function applyAll(Request $request, PayrollPeriod $payrollPeriod): RedirectResponse
    {
        $result = $this->advances->applyAllToPeriod($payrollPeriod);

        return redirect()
            ->route('accountant.advances.deduct', ['period_id' => $payrollPeriod->id])
            ->with('success', "Đã trừ {$result['applied']} tạm ứng, tổng ".number_format($result['total_amount'], 0, ',', '.').'₫. Bỏ qua: '.$result['skipped'].'.');
    }
}
