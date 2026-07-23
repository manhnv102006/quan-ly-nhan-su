<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Concerns\ResolvesLinkedEmployee;
use App\Http\Controllers\Controller;
use App\Models\SalaryAdvance;
use App\Services\AdvanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeAdvanceController extends Controller
{
    use ResolvesLinkedEmployee;

    public function __construct(
        private readonly AdvanceService $advances,
    ) {}

    public function index(): View
    {
        $employee = $this->linkedEmployee();

        $advances = SalaryAdvance::query()
            ->where('employee_id', $employee->id)
            ->with(['approver', 'rejecter'])
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->paginate(10);

        return view('employee.advances.index', [
            'advances' => $advances,
            'summary' => $this->advances->employeeSummary($employee),
            'maxAdvanceAmount' => $this->advances->maxAdvanceAmount($employee),
            'advanceSalaryCap' => $this->advances->advanceSalaryCap($employee),
            'usedAdvanceQuota' => $this->advances->usedAdvanceQuota($employee),
            'referenceSalary' => $this->advances->referenceSalary($employee),
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $employee = $this->linkedEmployee();
        $maxAmount = $this->advances->maxAdvanceAmount($employee);
        $cap = $this->advances->advanceSalaryCap($employee);

        if ($cap <= 0) {
            return redirect()
                ->route('employee.advances.index')
                ->with('error', 'Chưa có lương tham chiếu (hợp đồng/lương). Không thể ứng lương.');
        }

        if ($maxAmount <= 0) {
            return redirect()
                ->route('employee.advances.index')
                ->with('error', 'Bạn đã dùng hết hạn mức ứng lương (tối đa 50% lương tháng: '.number_format($cap, 0, ',', '.').'₫).');
        }

        $minAmount = min(SalaryAdvance::MIN_AMOUNT, $maxAmount);

        return view('employee.advances.create', [
            'maxAdvanceAmount' => $maxAmount,
            'minAdvanceAmount' => $minAmount,
            'advanceSalaryCap' => $cap,
            'usedAdvanceQuota' => $this->advances->usedAdvanceQuota($employee),
            'referenceSalary' => $this->advances->referenceSalary($employee),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $this->linkedEmployee();
        $maxAmount = $this->advances->maxAdvanceAmount($employee);
        $minAmount = min(SalaryAdvance::MIN_AMOUNT, $maxAmount);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:'.$minAmount.'|max:'.$maxAmount,
            'request_date' => 'required|date',
            'reason' => 'required|string|max:1000',
            'note' => 'nullable|string|max:2000',
        ], [
            'amount.min' => 'Số tiền ứng tối thiểu '.number_format($minAmount, 0, ',', '.').'₫.',
            'amount.max' => 'Số tiền ứng không được vượt hạn mức còn lại '.number_format($maxAmount, 0, ',', '.').'₫ (tối đa 50% lương tháng).',
            'reason.required' => 'Vui lòng nhập lý do ứng lương.',
        ]);

        try {
            $this->advances->submitRequest($employee, $validated, (int) auth()->id());
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('employee.advances.index')
            ->with('success', 'Đã gửi yêu cầu ứng lương tới kế toán. Số tiền được duyệt sẽ trừ vào lương thực lĩnh kỳ tới.');
    }

    public function show(SalaryAdvance $advance): View
    {
        $employee = $this->linkedEmployee();
        abort_unless($advance->employee_id === $employee->id, 403);

        $advance->load(['approver', 'rejecter', 'deductions.payrollPeriod']);

        return view('employee.advances.show', compact('advance'));
    }
}
