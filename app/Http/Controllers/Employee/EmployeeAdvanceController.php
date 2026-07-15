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
            'referenceSalary' => $this->advances->referenceSalary($employee),
        ]);
    }

    public function create(): View
    {
        $employee = $this->linkedEmployee();

        return view('employee.advances.create', [
            'maxAdvanceAmount' => $this->advances->maxAdvanceAmount($employee),
            'referenceSalary' => $this->advances->referenceSalary($employee),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $this->linkedEmployee();
        $maxAmount = $this->advances->maxAdvanceAmount($employee);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:100000|max:'.$maxAmount,
            'request_date' => 'required|date',
            'reason' => 'required|string|max:1000',
            'note' => 'nullable|string|max:2000',
        ], [
            'amount.min' => 'Số tiền ứng tối thiểu 100.000₫.',
            'amount.max' => 'Số tiền ứng không được vượt quá '.number_format($maxAmount, 0, ',', '.').'₫.',
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
