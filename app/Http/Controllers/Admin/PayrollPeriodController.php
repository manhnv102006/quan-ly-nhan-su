<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollPeriodController extends Controller
{
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
            'status' => 'required|in:open,closed',
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
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

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
            'status' => 'required|in:open,closed',
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
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        $payrollPeriod->update($validated);

        return redirect()
            ->route('admin.payroll-periods.index')
            ->with('success', 'Cập nhật kỳ lương thành công.');
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
}
