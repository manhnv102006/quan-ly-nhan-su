<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
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
    public function index(Request $request): View
    {
        $complaints = $this->complaints
            ->filteredQuery($request->query('status'), $request->query('search'))
            ->paginate(15)
            ->withQueryString();

        $stats = $this->complaints->dashboardStats();

        return view('accountant.payroll-complaints.index', compact('complaints', 'stats'));
    }

    public function show(PayrollComplaint $payrollComplaint): View
    {
        $payrollComplaint = $this->complaints->loadDetail($payrollComplaint);

        $nextPeriod = $payrollComplaint->payroll?->payrollPeriod
            ? $this->complaints->nextPeriodAfter($payrollComplaint->payroll->payrollPeriod)
            : null;

        return view('accountant.payroll-complaints.show', compact('payrollComplaint', 'nextPeriod'));
    }

    public function resolve(Request $request, PayrollComplaint $payrollComplaint): RedirectResponse
    {
        if (! $payrollComplaint->isAwaitingAccountant()) {
            return back()->with('error', 'Chỉ xử lý được khiếu nại đang chờ kế toán.');
        }

        $validated = $request->validate([
            'resolution_note' => ['required', 'string', 'max:2000'],
            'confirmed_adjustment_amount' => ['required', 'integer', 'min:1'],
        ], [
            'resolution_note.required' => 'Vui lòng ghi kết quả xử lý.',
            'confirmed_adjustment_amount.required' => 'Vui lòng nhập số tiền bổ sung chuyển sang tháng sau.',
            'confirmed_adjustment_amount.min' => 'Số tiền bổ sung phải lớn hơn 0.',
        ]);

        $this->complaints->resolveWithAdjustment(
            $payrollComplaint,
            (float) $validated['confirmed_adjustment_amount'],
            $validated['resolution_note'],
            (int) Auth::id(),
        );

        $next = $payrollComplaint->payroll?->payrollPeriod
            ? $this->complaints->nextPeriodAfter($payrollComplaint->payroll->payrollPeriod)
            : null;
        $periodLabel = $next
            ? str_pad((string) $next['month'], 2, '0', STR_PAD_LEFT).'/'.$next['year']
            : 'tháng sau';

        return back()->with(
            'success',
            'Đã xử lý khiếu nại. Số tiền '.number_format((float) $validated['confirmed_adjustment_amount'], 0, ',', '.')
            .' ₫ sẽ được cộng vào bảng lương tháng '.$periodLabel.' khi kế toán tính lương.'
        );
    }

    public function reject(Request $request, PayrollComplaint $payrollComplaint): RedirectResponse
    {
        if (! $payrollComplaint->isAwaitingAccountant()) {
            return back()->with('error', 'Chỉ từ chối được khiếu nại đang chờ kế toán.');
        }

        $validated = $request->validate([
            'reject_reason' => ['required', 'string', 'max:1000'],
        ], [
            'reject_reason.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        $payrollComplaint->update([
            'status' => PayrollComplaint::STATUS_REJECTED,
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'reject_reason' => $validated['reject_reason'],
        ]);

        return back()->with('success', 'Đã từ chối khiếu nại.');
    }
}
