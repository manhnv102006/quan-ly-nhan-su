<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Concerns\ResolvesCurrentEmployee;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRequestRejectRequest;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\LeaveApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LeaveApprovalController extends Controller
{
    use ResolvesCurrentEmployee;

    public function __construct(private readonly LeaveApprovalService $service)
    {
        $this->middleware(['auth', 'verified', 'role:manager', 'leave.approval.manager']);
    }

    public function index(Request $request): View
    {
        $manager = $this->currentManager();

        $query = LeaveRequest::query()
            ->with(['employee.department', 'employee.position', 'approver'])
            ->forManager($manager)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('employee_id'), function ($q) use ($request, $manager) {
                $employeeId = (int) $request->employee_id;
                $allowed = Employee::query()
                    ->managedByManager($manager)
                    ->whereKey($employeeId)
                    ->exists();

                abort_unless($allowed, 403, 'Bạn không có quyền xem đơn của nhân viên này.');

                $q->where('employee_id', $employeeId);
            })
            ->when($request->filled('start_from'), fn ($q) => $q->whereDate('start_date', '>=', $request->start_from))
            ->when($request->filled('start_to'), fn ($q) => $q->whereDate('start_date', '<=', $request->start_to))
            ->orderByDesc('created_at');

        $leaveRequests = $query->paginate(10)->withQueryString();

        $employees = Employee::query()
            ->managedByManager($manager)
            ->where('id', '!=', $manager->id)
            ->orderBy('full_name')
            ->get();

        return view('manager.leave-requests.index', [
            'leaveRequests' => $leaveRequests,
            'employees' => $employees,
            'filters' => $request->only(['status', 'employee_id', 'start_from', 'start_to']),
        ]);
    }

    public function show(LeaveRequest $leaveRequest): View
    {
        $manager = $this->currentManager();
        $this->authorizeForManager($leaveRequest, $manager);

        $leaveRequest->load(['employee.department', 'employee.position', 'approver', 'rejecter', 'histories.actor']);

        return view('manager.leave-requests.show', compact('leaveRequest'));
    }

    public function approve(LeaveRequest $leaveRequest): RedirectResponse
    {
        $manager = $this->currentManager();
        $this->authorizeForManager($leaveRequest, $manager);

        try {
            $this->service->approve($leaveRequest, (int) Auth::id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể duyệt đơn.');
        }

        return back()->with('success', 'Đã duyệt nghỉ phép.');
    }

    public function reject(LeaveRequestRejectRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $manager = $this->currentManager();
        $this->authorizeForManager($leaveRequest, $manager);

        try {
            $this->service->reject($leaveRequest, (int) Auth::id(), $request->validated('reject_reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể từ chối đơn.');
        }

        return back()->with('success', 'Đã từ chối nghỉ phép.');
    }

    protected function authorizeForManager(LeaveRequest $leaveRequest, Employee $manager): void
    {
        $employee = $leaveRequest->employee;

        if (! $employee?->isManagedBy($manager)) {
            abort(403, 'Bạn không có quyền duyệt yêu cầu này.');
        }
    }
}
