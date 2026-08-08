<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollComplaint;
use App\Services\PayrollComplaintService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PayrollComplaintController extends Controller
{
    public function __construct(
        private readonly PayrollComplaintService $complaints,
    ) {}

    public function index(): View
    {
        $employee = $this->employee();

        $complaintList = PayrollComplaint::query()
            ->with(['payroll.payrollPeriod'])
            ->where('employee_id', $employee->id)
            ->latest('id')
            ->paginate(10);

        return view('employee.payroll-complaints.index', compact('complaintList'));
    }

    public function create(Request $request): View
    {
        $employee = $this->employee();
        $payroll = null;

        if ($request->filled('payroll_id')) {
            $payroll = Payroll::with('payrollPeriod')
                ->where('employee_id', $employee->id)
                ->findOrFail($request->integer('payroll_id'));
        }

        $payrolls = Payroll::query()
            ->with('payrollPeriod')
            ->where('employee_id', $employee->id)
            ->join('payroll_periods', 'payroll_periods.id', '=', 'payrolls.payroll_period_id')
            ->orderByDesc('payroll_periods.year')
            ->orderByDesc('payroll_periods.month')
            ->select('payrolls.*')
            ->get();

        return view('employee.payroll-complaints.create', [
            'payrolls' => $payrolls,
            'selectedPayroll' => $payroll,
            'issueTypes' => PayrollComplaint::issueTypeOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payroll_id' => ['required', 'exists:payrolls,id'],
            'issue_type' => ['required', 'in:'.implode(',', array_keys(PayrollComplaint::issueTypeOptions()))],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'disputed_amount' => ['nullable', 'integer', 'min:0'],
        ], [
            'payroll_id.required' => 'Vui lòng chọn kỳ lương cần khiếu nại.',
            'issue_type.required' => 'Vui lòng chọn loại khiếu nại.',
            'subject.required' => 'Vui lòng nhập tiêu đề.',
            'description.required' => 'Vui lòng mô tả chi tiết vấn đề.',
        ]);

        $employee = $this->employee();
        $payroll = Payroll::findOrFail($validated['payroll_id']);
        $this->complaints->assertEmployeeOwnsPayroll($employee, $payroll);

        if (PayrollComplaint::query()
            ->where('employee_id', $employee->id)
            ->where('payroll_id', $payroll->id)
            ->whereIn('status', [PayrollComplaint::STATUS_PENDING, PayrollComplaint::STATUS_PROCESSING])
            ->exists()) {
            return back()->withInput()->with('error', 'Phiếu lương này đang có khiếu nại chưa xử lý xong.');
        }

        PayrollComplaint::create([
            'complaint_code' => $this->complaints->generateCode(),
            'employee_id' => $employee->id,
            'payroll_id' => $payroll->id,
            'issue_type' => $validated['issue_type'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'disputed_amount' => $validated['disputed_amount'] ?? null,
            'status' => PayrollComplaint::STATUS_PENDING,
        ]);

        return redirect()
            ->route('employee.payroll-complaints.index')
            ->with('success', 'Đã gửi khiếu nại lương. Quản lý sẽ xem xét và chuyển kế toán xử lý.');
    }

    public function show(PayrollComplaint $payrollComplaint): View
    {
        $employee = $this->employee();

        if ((int) $payrollComplaint->employee_id !== (int) $employee->id) {
            abort(403);
        }

        $payrollComplaint->load([
            'payroll.payrollPeriod',
            'managerConfirmer',
            'resolver',
            'rejecter',
        ]);

        return view('employee.payroll-complaints.show', compact('payrollComplaint'));
    }

    private function employee(): Employee
    {
        return Employee::where('user_id', Auth::id())->firstOrFail();
    }
}
