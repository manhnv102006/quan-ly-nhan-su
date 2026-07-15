<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\SalaryAdvance;
use App\Services\AdvanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvanceController extends Controller
{
    public function __construct(
        private readonly AdvanceService $advances,
    ) {}

    public function index(Request $request): View
    {
        $query = SalaryAdvance::query()->with(['employee.department', 'approver', 'rejecter']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('advance_code', 'like', "%{$search}%")
                    ->orWhereHas('employee', fn ($e) => $e
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%"));
            });
        }

        $advances = $query->orderByDesc('request_date')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('accountant.advances.index', [
            'advances' => $advances,
            'stats' => $this->advances->stats(),
        ]);
    }

    public function create(): View
    {
        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();

        return view('accountant.advances.create', compact('employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:'.SalaryAdvance::MIN_AMOUNT,
            'request_date' => 'required|date',
            'reason' => 'required|string|max:1000',
            'note' => 'nullable|string|max:2000',
        ], [
            'amount.min' => 'Số tiền tạm ứng tối thiểu '.number_format(SalaryAdvance::MIN_AMOUNT, 0, ',', '.').'₫',
        ]);

        SalaryAdvance::create([
            ...$validated,
            'advance_code' => SalaryAdvance::generateCode(),
            'status' => SalaryAdvance::STATUS_PENDING,
            'requested_by' => auth()->id(),
        ]);

        return redirect()
            ->route('accountant.advances.index')
            ->with('success', 'Đã tạo yêu cầu tạm ứng lương.');
    }

    public function show(SalaryAdvance $advance): View
    {
        $advance->load(['employee.department', 'approver', 'rejecter', 'requester', 'deductions.payrollPeriod', 'deductions.deductor']);

        return view('accountant.advances.show', compact('advance'));
    }

    public function approve(SalaryAdvance $advance): RedirectResponse
    {
        $this->advances->approve($advance);

        return back()->with('success', "Đã duyệt tạm ứng {$advance->advance_code}.");
    }

    public function reject(Request $request, SalaryAdvance $advance): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $this->advances->reject($advance, $validated['rejection_reason']);

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

        $this->advances->applyDeduction(
            $advance,
            $payroll,
            isset($validated['amount']) ? (float) $validated['amount'] : null,
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
