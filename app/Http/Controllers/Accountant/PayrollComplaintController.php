<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\PayrollComplaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PayrollComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $query = PayrollComplaint::query()
            ->with(['employee.department', 'employee.position', 'payroll.payrollPeriod'])
            ->latest('id');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('complaint_code', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($employee) use ($search) {
                        $employee->where('full_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%");
                    });
            });
        }

        $complaints = $query->paginate(15)->withQueryString();

        $stats = [
            'pending' => PayrollComplaint::where('status', PayrollComplaint::STATUS_PENDING)->count(),
            'processing' => PayrollComplaint::where('status', PayrollComplaint::STATUS_PROCESSING)->count(),
            'resolved' => PayrollComplaint::where('status', PayrollComplaint::STATUS_RESOLVED)->count(),
            'rejected' => PayrollComplaint::where('status', PayrollComplaint::STATUS_REJECTED)->count(),
        ];

        return view('accountant.payroll-complaints.index', compact('complaints', 'stats'));
    }

    public function show(PayrollComplaint $payrollComplaint): View
    {
        $payrollComplaint->load([
            'employee.department',
            'employee.position',
            'payroll.payrollPeriod',
            'managerConfirmer',
            'resolver',
            'rejecter',
        ]);

        return view('accountant.payroll-complaints.show', compact('payrollComplaint'));
    }

    public function resolve(Request $request, PayrollComplaint $payrollComplaint): RedirectResponse
    {
        if (! $payrollComplaint->isProcessing()) {
            return back()->with('error', 'Chỉ xử lý được khiếu nại đang chờ kế toán.');
        }

        $validated = $request->validate([
            'resolution_note' => ['required', 'string', 'max:2000'],
        ], [
            'resolution_note.required' => 'Vui lòng ghi kết quả xử lý.',
        ]);

        $payrollComplaint->update([
            'status' => PayrollComplaint::STATUS_RESOLVED,
            'resolution_note' => $validated['resolution_note'],
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Đã đánh dấu khiếu nại là đã xử lý.');
    }

    public function reject(Request $request, PayrollComplaint $payrollComplaint): RedirectResponse
    {
        if (! $payrollComplaint->isProcessing()) {
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
