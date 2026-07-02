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
            ->withSum('payrolls', 'total_salary')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'asc')
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

        return view('admin.payroll-periods.show', [
            'payrollPeriod' => $payrollPeriod,
            'stats' => $stats,
            'departmentSummaries' => \App\Support\DepartmentSummaryBuilder::forPayrollPeriod($payrollPeriod),
        ]);
    }

    public function department(PayrollPeriod $payrollPeriod, \App\Models\Department $department): View
    {
        $payrolls = $payrollPeriod->payrolls()
            ->whereHas('employee', fn($q) => $q->where('department_id', $department->id))
            ->with(['employee'])
            ->latest()
            ->paginate(10);

        // Xác định trạng thái của riêng phòng ban này
        $deptPayrolls = $payrollPeriod->payrolls()
            ->whereHas('employee', fn($q) => $q->where('department_id', $department->id))
            ->get();

        if ($deptPayrolls->isEmpty()) {
            $departmentStatus = 'open';
        } else {
            $statuses = $deptPayrolls->pluck('status')->unique();
            if ($statuses->contains('closed')) {
                $departmentStatus = 'closed';
            } elseif ($statuses->contains('paid')) {
                $departmentStatus = 'paid';
            } elseif ($statuses->contains('approved')) {
                $departmentStatus = 'approved';
            } else {
                $departmentStatus = 'calculated';
            }
        }

        return view('admin.payroll-periods.department', compact('payrollPeriod', 'department', 'payrolls', 'departmentStatus'));
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

    public function calculate(Request $request, PayrollPeriod $payrollPeriod, PayrollService $payrollService): RedirectResponse
    {
        if (!$payrollPeriod->isOpen()) {
            return redirect()->back()->with('error', 'Kỳ lương phải ở trạng thái mở mới có thể tính lương.');
        }

        $departmentId = $request->input('department_id');
        $result = $payrollService->calculatePayrollForPeriod($payrollPeriod, $departmentId);

        if ($result === 'already_exists') {
            return redirect()->back()->with('error', 'Kỳ lương này (hoặc phòng ban này) đã được tính lương trước đó.');
        }

        if ($result === 'no_employees') {
            return redirect()->back()->with('error', 'Không có nhân viên hoạt động nào.');
        }

        return redirect()->back()->with('success', 'Tính lương tự động thành công.');
    }

    public function recalculate(Request $request, PayrollPeriod $payrollPeriod, PayrollService $payrollService): RedirectResponse
    {
        $departmentId = $request->input('department_id');
        $result = $payrollService->recalculatePayrollForPeriod($payrollPeriod, $departmentId);

        if ($result === 'invalid_status') {
            return redirect()->back()->with('error', 'Trạng thái kỳ lương không hợp lệ để tính lại.');
        }

        if ($result === 'no_employees') {
            return redirect()->back()->with('error', 'Không có nhân viên hoạt động nào.');
        }

        return redirect()->back()->with('success', 'Đã tính lại lương thành công.');
    }

    public function approve(Request $request, PayrollPeriod $payrollPeriod): RedirectResponse
    {
        $departmentId = $request->input('department_id');
        
        $query = $payrollPeriod->payrolls();
        if ($departmentId) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $departmentId));
        }

        if ((clone $query)->count() === 0) {
            return redirect()->back()->with('error', 'Chưa có bảng lương nào được tính để duyệt.');
        }

        $query->update([
            'status' => 'approved',
            'approved_by' => auth()->id() ?? 1,
            'approved_at' => now(),
        ]);

        // Cập nhật trạng thái kỳ lương chung thành 'approved' nếu tất cả đã được duyệt
        $hasUnapproved = $payrollPeriod->payrolls()->where(function($q) {
            $q->whereNull('status')->orWhere('status', '!=', 'approved');
        })->exists();

        if (!$hasUnapproved) {
            $payrollPeriod->update([
                'status' => 'approved',
                'approved_by' => auth()->id() ?? 1,
                'approved_at' => now(),
            ]);
            $this->autoNotifications->payrollApproved($payrollPeriod);
        }

        return redirect()->back()->with('success', 'Đã duyệt bảng lương thành công.');
    }

    public function pay(Request $request, PayrollPeriod $payrollPeriod): RedirectResponse
    {
        $departmentId = $request->input('department_id');
        
        $query = $payrollPeriod->payrolls();
        if ($departmentId) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $departmentId));
        }

        if ((clone $query)->count() === 0) {
            return redirect()->back()->with('error', 'Chưa có bảng lương nào.');
        }

        // Chỉ cho phép chi trả khi tất cả các dòng của bộ phận này đã được duyệt
        $hasUnapproved = (clone $query)->where(fn($q) => $q->whereNull('status')->orWhere('status', '!=', 'approved'))->exists();
        if ($hasUnapproved) {
            return redirect()->back()->with('error', 'Chỉ có thể chi trả sau khi bảng lương đã được duyệt.');
        }

        $query->update([
            'status' => 'paid',
            'paid_by' => auth()->id() ?? 1,
            'paid_at' => now(),
        ]);

        // Cập nhật trạng thái kỳ lương chung thành 'paid' nếu tất cả đã được chi trả
        $hasUnpaid = $payrollPeriod->payrolls()->where('status', '!=', 'paid')->exists();
        if (!$hasUnpaid) {
            $payrollPeriod->update([
                'status' => 'paid',
                'paid_by' => auth()->id() ?? 1,
                'paid_at' => now(),
            ]);
            $this->autoNotifications->payrollPaid($payrollPeriod);
        }

        return redirect()->back()->with('success', 'Đã chi trả lương thành công.');
    }

    public function close(Request $request, PayrollPeriod $payrollPeriod): RedirectResponse
    {
        $departmentId = $request->input('department_id');
        
        $query = $payrollPeriod->payrolls();
        if ($departmentId) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $departmentId));
        }

        if ((clone $query)->count() === 0) {
            return redirect()->back()->with('error', 'Chưa có bảng lương nào.');
        }

        // Chỉ cho phép đóng khi tất cả các dòng của bộ phận này đã được chi trả
        $hasUnpaid = (clone $query)->where('status', '!=', 'paid')->exists();
        if ($hasUnpaid) {
            return redirect()->back()->with('error', 'Chỉ có thể đóng kỳ lương sau khi đã chi trả lương.');
        }

        $query->update([
            'status' => 'closed',
        ]);

        // Cập nhật trạng thái kỳ lương chung thành 'closed' nếu tất cả đã được đóng
        $hasUnclosed = $payrollPeriod->payrolls()->where('status', '!=', 'closed')->exists();
        if (!$hasUnclosed) {
            $payrollPeriod->update([
                'status' => 'closed',
            ]);
        }

        return redirect()->back()->with('success', 'Đã đóng bảng lương thành công.');
    }
}

