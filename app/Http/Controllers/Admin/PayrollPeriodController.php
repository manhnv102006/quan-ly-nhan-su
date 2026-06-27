<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use App\Services\AutoNotificationService;
use App\Services\PayrollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollPeriodController extends Controller
{
    public function __construct(
        private AutoNotificationService $autoNotifications,
    ) {}

    public function index(Request $request): View
    {
        $periods = PayrollPeriod::query()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => PayrollPeriod::count(),
            'open' => PayrollPeriod::where('status', 'open')->count(),
            'calculated' => PayrollPeriod::where('status', 'calculated')->count(),
            'approved' => PayrollPeriod::where('status', 'approved')->count(),
            'paid' => PayrollPeriod::where('status', 'paid')->count(),
            'closed' => PayrollPeriod::where('status', 'closed')->count(),
        ];

        return view('admin.payroll-periods.index', compact('periods', 'stats'));
    }

    public function create(): View
    {
        return view('admin.payroll-periods.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'month' => [
                'required',
                'integer',
                'min:1',
                'max:12',
                Rule::unique('payroll_periods')->where(function ($query) use ($request) {
                    return $query->where('year', $request->year);
                }),
            ],
            'year' => 'required|integer|min:2020|max:2100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ], [
            'name.required' => 'Tên kỳ lương là bắt buộc',
            'month.required' => 'Tháng là bắt buộc',
            'month.integer' => 'Tháng phải là số nguyên',
            'month.min' => 'Tháng phải từ 1 đến 12',
            'month.max' => 'Tháng phải từ 1 đến 12',
            'month.unique' => 'Kỳ lương của tháng/năm này đã tồn tại trong hệ thống',
            'year.required' => 'Năm là bắt buộc',
            'year.integer' => 'Năm phải là số nguyên',
            'year.min' => 'Năm không hợp lệ',
            'year.max' => 'Năm không hợp lệ',
            'start_date.required' => 'Ngày bắt đầu là bắt buộc',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ',
            'end_date.required' => 'Ngày kết thúc là bắt buộc',
            'end_date.date' => 'Ngày kết thúc không hợp lệ',
            'end_date.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu',
        ]);

        $validated['status'] = 'open';

        PayrollPeriod::create($validated);

        return redirect()
            ->route('admin.payroll-periods.index')
            ->with('success', 'Thêm kỳ lương mới thành công.');
    }

    public function edit(PayrollPeriod $payrollPeriod): View
    {
        return view('admin.payroll-periods.edit', compact('payrollPeriod'));
    }

    public function update(Request $request, PayrollPeriod $payrollPeriod): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'month' => [
                'required',
                'integer',
                'min:1',
                'max:12',
                Rule::unique('payroll_periods')->where(function ($query) use ($request, $payrollPeriod) {
                    return $query->where('year', $request->year);
                })->ignore($payrollPeriod->id),
            ],
            'year' => 'required|integer|min:2020|max:2100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ], [
            'name.required' => 'Tên kỳ lương là bắt buộc',
            'month.required' => 'Tháng là bắt buộc',
            'month.integer' => 'Tháng phải là số nguyên',
            'month.min' => 'Tháng phải từ 1 đến 12',
            'month.max' => 'Tháng phải từ 1 đến 12',
            'month.unique' => 'Kỳ lương của tháng/năm này đã tồn tại trong hệ thống',
            'year.required' => 'Năm là bắt buộc',
            'year.integer' => 'Năm phải là số nguyên',
            'year.min' => 'Năm không hợp lệ',
            'year.max' => 'Năm không hợp lệ',
            'start_date.required' => 'Ngày bắt đầu là bắt buộc',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ',
            'end_date.required' => 'Ngày kết thúc là bắt buộc',
            'end_date.date' => 'Ngày kết thúc không hợp lệ',
            'end_date.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu',
        ]);

        $payrollPeriod->update($validated);

        return redirect()
            ->route('admin.payroll-periods.index')
            ->with('success', 'Cập nhật kỳ lương thành công.');
    }

    public function show(PayrollPeriod $payrollPeriod): View
    {
        $payrolls = $payrollPeriod->payrolls()
            ->with(['employee'])
            ->latest()
            ->paginate(10);

        $totalCount = $payrollPeriod->payrolls()->count();
        $totalSalary = $payrollPeriod->payrolls()->sum('total_salary');

        // Lương đã chi trả phụ thuộc vào trạng thái của kỳ lương (paid hoặc closed)
        $isPaidOrClosed = in_array($payrollPeriod->status, ['paid', 'closed']);
        $stats = [
            'total_count' => $totalCount,
            'total_salary' => $totalSalary,
            'paid_salary' => $isPaidOrClosed ? $totalSalary : 0,
            'unpaid_salary' => $isPaidOrClosed ? 0 : $totalSalary,
        ];

        return view('admin.payroll-periods.show', compact('payrollPeriod', 'payrolls', 'stats'));
    }

    public function destroy(PayrollPeriod $payrollPeriod): RedirectResponse
    {
        if ($payrollPeriod->payrolls()->exists()) {
            return redirect()
                ->route('admin.payroll-periods.index')
                ->with('error', 'Không thể xóa kỳ lương này vì đã có bảng lương liên kết.');
        }

        $payrollPeriod->delete();

        return redirect()
            ->route('admin.payroll-periods.index')
            ->with('success', 'Xóa kỳ lương thành công.');
    }

    public function calculate(PayrollPeriod $payrollPeriod, PayrollService $payrollService): RedirectResponse
    {
        if (!$payrollPeriod->isOpen()) {
            return redirect()->back()->with('error', 'Kỳ lương phải ở trạng thái mở mới có thể tính lương.');
        }

        $result = $payrollService->calculatePayrollForPeriod($payrollPeriod);

        if ($result === 'already_exists') {
            return redirect()->back()->with('error', 'Kỳ lương này đã được tính lương trước đó.');
        }

        if ($result === 'no_employees') {
            return redirect()->back()->with('error', 'Không có nhân viên hoạt động nào trong kỳ này.');
        }

        return redirect()->back()->with('success', 'Tính lương tự động cho toàn bộ nhân viên thành công.');
    }

    public function approve(PayrollPeriod $payrollPeriod): RedirectResponse
    {
        if (!$payrollPeriod->isCalculated()) {
            return redirect()->back()->with('error', 'Chỉ có thể duyệt kỳ lương sau khi đã tính lương.');
        }

        $payrollPeriod->update([
            'status' => 'approved',
            'approved_by' => auth()->id() ?? 1,
            'approved_at' => now(),
        ]);

        $this->autoNotifications->payrollApproved($payrollPeriod);

        return redirect()->back()->with('success', 'Đã duyệt toàn bộ kỳ lương thành công.');
    }

    public function pay(PayrollPeriod $payrollPeriod): RedirectResponse
    {
        if (!$payrollPeriod->isApproved()) {
            return redirect()->back()->with('error', 'Chỉ có thể chi trả kỳ lương sau khi đã được phê duyệt.');
        }

        $payrollPeriod->update([
            'status' => 'paid',
            'paid_by' => auth()->id() ?? 1,
            'paid_at' => now(),
        ]);

        $this->autoNotifications->payrollPaid($payrollPeriod);

        return redirect()->back()->with('success', 'Đã thực hiện chi trả lương cho toàn bộ nhân viên.');
    }

    public function close(PayrollPeriod $payrollPeriod): RedirectResponse
    {
        if (!$payrollPeriod->isPaid()) {
            return redirect()->back()->with('error', 'Chỉ có thể đóng kỳ lương sau khi đã chi trả lương.');
        }

        $payrollPeriod->update([
            'status' => 'closed',
        ]);

        return redirect()->back()->with('success', 'Kỳ lương đã được đóng thành công.');
    }
}

