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
        $query = Payroll::query()->with(['employee', 'payrollPeriod', 'approver', 'payer']);

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

    public function generate(Request $request): RedirectResponse
    {
        $request->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id',
        ], [
            'payroll_period_id.required' => 'Vui lòng chọn kỳ lương cần tính',
            'payroll_period_id.exists' => 'Kỳ lương không tồn tại trong hệ thống',
        ]);

        $period = PayrollPeriod::findOrFail($request->payroll_period_id);
        $result = $this->payrollService->calculatePayrollForPeriod($period);

        if ($result === 'already_exists') {
            return redirect()
                ->back()
                ->with('error', 'Kỳ lương này đã được tính trước đó. Không thể tính lại để tránh trùng dữ liệu.');
        }

        if ($result === 'no_employees') {
            return redirect()
                ->back()
                ->with('error', 'Không tìm thấy nhân viên nào đang hoạt động để tính lương.');
        }

        return redirect()
            ->route('admin.payrolls')
            ->with('success', 'Tính lương tự động cho kỳ lương thành công.');
    }

    public function submit(Payroll $payroll): RedirectResponse
    {
        if (!$payroll->isDraft()) {
            return redirect()
                ->back()
                ->with('error', 'Chỉ bảng lương ở trạng thái Nháp mới có thể gửi duyệt.');
        }

        $payroll->update([
            'status' => 'pending',
        ]);

        return redirect()
            ->route('admin.payrolls')
            ->with('success', 'Gửi duyệt bảng lương thành công.');
    }

    public function approve(Payroll $payroll): RedirectResponse
    {
        if (!$payroll->isPending()) {
            return redirect()
                ->back()
                ->with('error', 'Chỉ bảng lương ở trạng thái Chờ duyệt mới có thể phê duyệt.');
        }

        $payroll->update([
            'status' => 'approved',
            'approved_by' => Auth::id() ?? 1,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('admin.payrolls')
            ->with('success', 'Phê duyệt bảng lương thành công.');
    }

    public function pay(Payroll $payroll): RedirectResponse
    {
        if (!$payroll->isApproved()) {
            return redirect()
                ->back()
                ->with('error', 'Chỉ bảng lương đã được duyệt mới có thể chi trả.');
        }

        $payroll->update([
            'status' => 'paid',
            'paid_by' => Auth::id() ?? 1,
            'paid_at' => now(),
        ]);

        return redirect()
            ->route('admin.payrolls')
            ->with('success', 'Đánh dấu đã chi trả lương thành công.');
    }
}
