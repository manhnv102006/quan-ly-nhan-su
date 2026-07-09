<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use App\Models\Payroll;
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
        $selectedYear = (int) $request->input('year', now()->year);
        $selectedYear = max(2020, min(2100, $selectedYear));

        $dbYears = PayrollPeriod::query()
            ->select('year')
            ->distinct()
            ->pluck('year')
            ->map(fn ($year) => (int) $year);

        $minYear = min($dbYears->min() ?? $selectedYear, now()->year - 2, $selectedYear);
        $maxYear = max($dbYears->max() ?? $selectedYear, now()->year + 3, $selectedYear);

        $availableYears = collect(range($maxYear, $minYear))->values();

        $periodsByMonth = PayrollPeriod::query()
            ->withSum('payrolls', 'total_salary')
            ->where('year', $selectedYear)
            ->get()
            ->keyBy('month');

        $totalDepartments = \App\Models\Department::where('status', 'active')
            ->whereHas('employees', fn ($q) => $q->where('status', 'active'))
            ->count();

        $monthSlots = collect(range(1, 12))->map(function (int $month) use ($periodsByMonth, $selectedYear, $totalDepartments) {
            $period = $periodsByMonth->get($month);

            if ($period) {
                $this->enrichPeriod($period, $totalDepartments);
            }

            $workRange = $this->monthWorkRange($selectedYear, $month);

            return [
                'month' => $month,
                'period' => $period,
                'name' => $period?->name ?? "Kỳ lương tháng {$workRange['month_label']}/{$selectedYear}",
                'work_range' => $period
                    ? ($period->start_date?->format('d/m/Y') . ' - ' . $period->end_date?->format('d/m/Y'))
                    : $workRange['label'],
            ];
        });

        $createdCount = PayrollPeriod::where('year', $selectedYear)->count();

        $stats = [
            'total' => $createdCount,
            'missing' => 12 - $createdCount,
            'open' => PayrollPeriod::where('year', $selectedYear)->where('status', 'open')->count(),
            'calculated' => PayrollPeriod::where('year', $selectedYear)->where('status', 'calculated')->count(),
            'approved' => PayrollPeriod::where('year', $selectedYear)->where('status', 'approved')->count(),
            'paid' => PayrollPeriod::where('year', $selectedYear)->where('status', 'paid')->count(),
            'closed' => PayrollPeriod::where('year', $selectedYear)->where('status', 'closed')->count(),
        ];

        return view('admin.payroll-periods.index', compact('monthSlots', 'stats', 'selectedYear', 'availableYears'));
    }

    public function create(Request $request): View
    {
        return view('admin.payroll-periods.create', [
            'prefillMonth' => $request->integer('month') ?: null,
            'prefillYear' => $request->integer('year') ?: null,
        ]);
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
            ->route('admin.payroll-periods.index', ['year' => $validated['year']])
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

    public function trash(Request $request): View
    {
        $periods = PayrollPeriod::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.payroll-periods.trash', compact('periods'));
    }

    public function destroy(PayrollPeriod $payrollPeriod): RedirectResponse
    {
        // Xóa mềm tất cả các bản ghi lương liên kết trước
        $payrollPeriod->payrolls()->delete();

        // Xóa mềm kỳ lương
        $payrollPeriod->delete();

        return redirect()
            ->route('admin.payroll-periods.index', ['year' => $payrollPeriod->year])
            ->with('success', 'Đã xóa mềm kỳ lương thành công. Bạn có thể khôi phục từ Thùng rác.');
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $period = PayrollPeriod::onlyTrashed()->findOrFail($id);
        $period->restore();

        // Khôi phục tất cả bảng lương liên kết
        Payroll::onlyTrashed()->where('payroll_period_id', $id)->restore();

        return redirect()
            ->route('admin.payroll-periods.trash')
            ->with('success', 'Đã khôi phục kỳ lương và các bảng lương liên kết thành công.');
    }

    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        $period = PayrollPeriod::onlyTrashed()->findOrFail($id);

        // Xóa vĩnh viễn tất cả bảng lương liên kết
        Payroll::onlyTrashed()->where('payroll_period_id', $id)->forceDelete();

        // Xóa vĩnh viễn kỳ lương
        $period->forceDelete();

        return redirect()
            ->route('admin.payroll-periods.trash')
            ->with('success', 'Đã xóa vĩnh viễn kỳ lương khỏi hệ thống.');
    }

    public function calculate(Request $request, PayrollPeriod $payrollPeriod, PayrollService $payrollService): RedirectResponse
    {
        if (!in_array($payrollPeriod->status, ['open', 'calculated'])) {
            return redirect()->back()->with('error', 'Kỳ lương phải ở trạng thái mở hoặc đã tính mới có thể tính lương.');
        }

        $departmentId = $request->input('department_id');
        $result = $payrollService->calculatePayrollForPeriod($payrollPeriod, $departmentId);

        if ($result === 'already_exists') {
            return redirect()->back()->with('error', 'Kỳ lương này (hoặc phòng ban này) đã được tính lương trước đó.');
        }

        if ($result === 'no_employees') {
            return redirect()->back()->with('error', 'Không có nhân viên hoạt động nào.');
        }

        $this->syncPeriodStatus($payrollPeriod);

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

        $this->syncPeriodStatus($payrollPeriod);

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

        $this->syncPeriodStatus($payrollPeriod);

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

        $this->syncPeriodStatus($payrollPeriod);

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

        $this->syncPeriodStatus($payrollPeriod);

        return redirect()->back()->with('success', 'Đã đóng bảng lương thành công.');
    }

    private function enrichPeriod(PayrollPeriod $period, int $totalDepartments): void
    {
        $payrolls = Payroll::query()
            ->where('payroll_period_id', $period->id)
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->where('employees.status', 'active')
            ->get(['employees.department_id', 'payrolls.status', 'payrolls.total_salary']);

        $calculatedDepts = $payrolls->pluck('department_id')->unique();
        $period->is_all_calculated = ($totalDepartments > 0 && $calculatedDepts->count() >= $totalDepartments);

        $approvedDepts = $payrolls->groupBy('department_id')->filter(function ($group) {
            return $group->every(fn ($p) => in_array($p->status, ['approved', 'paid', 'closed']));
        });
        $period->is_all_approved = ($totalDepartments > 0 && $approvedDepts->count() >= $totalDepartments);

        $paidDepts = $payrolls->groupBy('department_id')->filter(function ($group) {
            return $group->every(fn ($p) => in_array($p->status, ['paid', 'closed']));
        });
        $period->is_all_paid = ($totalDepartments > 0 && $paidDepts->count() >= $totalDepartments);

        $closedDepts = $payrolls->groupBy('department_id')->filter(function ($group) {
            return $group->every(fn ($p) => $p->status === 'closed');
        });
        $period->is_all_closed = ($totalDepartments > 0 && $closedDepts->count() >= $totalDepartments);

        $period->paid_salary_sum = $payrolls->filter(fn ($p) => in_array($p->status, ['paid', 'closed']))->sum('total_salary');
        $period->unpaid_salary_sum = $payrolls->filter(fn ($p) => ! in_array($p->status, ['paid', 'closed']))->sum('total_salary');
    }

    private function monthWorkRange(int $year, int $month): array
    {
        $start = \Carbon\Carbon::createFromDate($year, $month, 1);
        $end = $start->copy()->endOfMonth();

        return [
            'month_label' => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
            'label' => $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'),
        ];
    }

    private function syncPeriodStatus(PayrollPeriod $payrollPeriod): void
    {
        $totalActiveDepartmentsCount = \App\Models\Department::where('status', 'active')
            ->whereHas('employees', fn($q) => $q->where('status', 'active'))
            ->count();

        $payrolls = $payrollPeriod->payrolls()
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->where('employees.status', 'active')
            ->get(['employees.department_id', 'payrolls.status']);

        $calculatedDeptsCount = $payrolls->pluck('department_id')->unique()->count();
        $allCalculated = ($totalActiveDepartmentsCount > 0 && $calculatedDeptsCount >= $totalActiveDepartmentsCount);

        if ($payrolls->isEmpty()) {
            $newStatus = 'open';
        } else {
            $statuses = $payrolls->pluck('status')->toArray();

            $allClosed = $allCalculated && !in_array('calculated', $statuses) && !in_array('approved', $statuses) && !in_array('paid', $statuses);
            $allPaid = $allCalculated && !in_array('calculated', $statuses) && !in_array('approved', $statuses);
            $allApproved = $allCalculated && !in_array('calculated', $statuses);

            if ($allClosed) {
                $newStatus = 'closed';
            } elseif ($allPaid) {
                $newStatus = 'paid';
            } elseif ($allApproved) {
                $newStatus = 'approved';
            } else {
                $newStatus = 'calculated';
            }
        }

        if ($payrollPeriod->status !== $newStatus) {
            $payrollPeriod->update(['status' => $newStatus]);
            
            if ($newStatus === 'approved') {
                $this->autoNotifications->payrollApproved($payrollPeriod);
            } elseif ($newStatus === 'paid') {
                $this->autoNotifications->payrollPaid($payrollPeriod);
            }
        }
    }
}

