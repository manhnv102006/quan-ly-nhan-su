<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\EarlyLeaveRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EarlyLeaveApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $managerEmployee = Employee::where('user_id', Auth::id())->first();

        $query = EarlyLeaveRequest::with(['employee.department', 'employee.position', 'approver'])
            ->latest('request_date')
            ->latest('id');

        // Manager chỉ thấy nhân viên trong phòng của mình
        if ($managerEmployee?->department_id) {
            $query->whereHas('employee', fn ($q) =>
                $q->where('department_id', $managerEmployee->department_id)
            );
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(15)->withQueryString();

        $pendingCount = (clone $query->getQuery())->where('status', 'pending')->count();

        return view('manager.early-leave.index', compact('requests', 'pendingCount'));
    }

    public function show(EarlyLeaveRequest $earlyLeaveRequest): View
    {
        $earlyLeaveRequest->load(['employee.department', 'employee.position', 'approver', 'rejecter']);
        return view('manager.early-leave.show', compact('earlyLeaveRequest'));
    }

    public function approve(EarlyLeaveRequest $earlyLeaveRequest): RedirectResponse
    {
        if (! $earlyLeaveRequest->isPending()) {
            return back()->with('error', 'Đơn này đã được xử lý.');
        }

        $earlyLeaveRequest->update([
            'status'      => EarlyLeaveRequest::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Đã duyệt đơn xin về sớm.');
    }

    public function reject(Request $request, EarlyLeaveRequest $earlyLeaveRequest): RedirectResponse
    {
        if (! $earlyLeaveRequest->isPending()) {
            return back()->with('error', 'Đơn này đã được xử lý.');
        }

        $request->validate([
            'reject_reason' => ['required', 'string', 'max:500'],
        ], [
            'reject_reason.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        $earlyLeaveRequest->update([
            'status'        => EarlyLeaveRequest::STATUS_REJECTED,
            'rejected_by'   => Auth::id(),
            'rejected_at'   => now(),
            'reject_reason' => $request->reject_reason,
        ]);

        return back()->with('success', 'Đã từ chối đơn xin về sớm.');
    }
}
