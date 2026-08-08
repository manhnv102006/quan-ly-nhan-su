<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollComplaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PayrollComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $managerEmployee = Employee::where('user_id', Auth::id())->first();

        $query = PayrollComplaint::query()
            ->with(['employee.department', 'employee.position', 'payroll.payrollPeriod'])
            ->latest('id');

        if ($managerEmployee?->department_id) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $managerEmployee->department_id));
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $complaints = $query->paginate(15)->withQueryString();

        $pendingCount = PayrollComplaint::query()
            ->when($managerEmployee?->department_id, fn ($q) => $q->whereHas('employee', fn ($eq) => $eq->where('department_id', $managerEmployee->department_id)))
            ->where('status', PayrollComplaint::STATUS_PENDING)
            ->count();

        return view('manager.payroll-complaints.index', compact('complaints', 'pendingCount'));
    }

    public function show(PayrollComplaint $payrollComplaint): View
    {
        $this->assertSameDepartment($payrollComplaint);

        $payrollComplaint->load([
            'employee.department',
            'employee.position',
            'payroll.payrollPeriod',
            'managerConfirmer',
            'resolver',
            'rejecter',
        ]);

        return view('manager.payroll-complaints.show', compact('payrollComplaint'));
    }

    public function confirm(Request $request, PayrollComplaint $payrollComplaint): RedirectResponse
    {
        $this->assertSameDepartment($payrollComplaint);

        if (! $payrollComplaint->isPending()) {
            return back()->with('error', 'Khiếu nại này đã được xử lý.');
        }

        $validated = $request->validate([
            'manager_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $payrollComplaint->update([
            'status' => PayrollComplaint::STATUS_PROCESSING,
            'manager_note' => $validated['manager_note'] ?? null,
            'manager_confirmed_by' => Auth::id(),
            'manager_confirmed_at' => now(),
        ]);

        return back()->with('success', 'Đã chuyển khiếu nại cho kế toán xử lý.');
    }

    public function reject(Request $request, PayrollComplaint $payrollComplaint): RedirectResponse
    {
        $this->assertSameDepartment($payrollComplaint);

        if (! $payrollComplaint->isPending()) {
            return back()->with('error', 'Khiếu nại này đã được xử lý.');
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

    private function assertSameDepartment(PayrollComplaint $complaint): void
    {
        $managerEmployee = Employee::where('user_id', Auth::id())->first();
        $complaint->loadMissing('employee');

        if (! $managerEmployee?->department_id
            || (int) $complaint->employee?->department_id !== (int) $managerEmployee->department_id) {
            abort(403, 'Bạn chỉ được xem khiếu nại của nhân viên trong phòng ban mình.');
        }
    }
}
