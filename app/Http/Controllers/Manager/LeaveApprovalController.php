<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LeaveApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $manager = $this->currentManager();

        $query = LeaveRequest::with(['employee.department', 'employee.position', 'approver'])
            ->whereHas('employee', function ($q) use ($manager) {
                $q->where('manager_id', $manager->id);
            })
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('employee_id'), fn($q) => $q->where('employee_id', $request->employee_id))
            ->when($request->filled('start_from'), fn($q) => $q->whereDate('start_date', '>=', $request->start_from))
            ->when($request->filled('start_to'), fn($q) => $q->whereDate('start_date', '<=', $request->start_to))
            ->orderByDesc('created_at');

        $leaveRequests = $query->paginate(15)->withQueryString();
        $employees = Employee::where('manager_id', $manager->id)->orderBy('full_name')->get();

        return view('manager.leave-requests.index', [
            'leaveRequests' => $leaveRequests,
            'filters' => $request->only(['status']),
            'employees' => $employees,
            'filters' => $request->only(['status', 'employee_id', 'start_from', 'start_to']),
        ]);
    }

    public function show(LeaveRequest $leaveRequest): View
    {
        $manager = $this->currentManager();
        $this->authorizeForManager($leaveRequest, $manager);

        $leaveRequest->load(['employee.department', 'employee.position', 'approver']);

        return view('manager.leave-requests.show', compact('leaveRequest'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $manager = $this->currentManager();
        $this->authorizeForManager($leaveRequest, $manager);

        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'reject_reason' => null,
        ]);

        return back()->with('success', 'Đã duyệt nghỉ phép.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $manager = $this->currentManager();
        $this->authorizeForManager($leaveRequest, $manager);

        $request->validate([
            'reject_reason' => ['required', 'string', 'max:500'],
        ]);

        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_REJECTED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'reject_reason' => $request->reject_reason,
        ]);

        return back()->with('success', 'Đã từ chối nghỉ phép.');
    }

    protected function currentManager(): Employee
    {
        $manager = Employee::where('user_id', Auth::id())->first();
        abort_if(! $manager, 403, 'Không tìm thấy thông tin nhân viên quản lý.');

        return $manager;
    }

    protected function authorizeForManager(LeaveRequest $leaveRequest, Employee $manager): void
    {
        if ($leaveRequest->employee?->manager_id !== $manager->id) {
            abort(403, 'Bạn không có quyền duyệt yêu cầu này.');
        }
    }
}
