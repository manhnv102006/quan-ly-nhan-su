<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRequestRejectRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\LeaveApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function __construct(private readonly LeaveApprovalService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = Auth::user();
        $employeeProfile = Employee::where('user_id', $user->id)->first();

        $statsQuery = LeaveRequest::query();
        $query = LeaveRequest::query()->with(['employee.department', 'approver']);

        if ($user->role->name === 'manager') {
            $department = $this->resolveManagedDepartment($employeeProfile);
            $scope = function ($q) use ($department) {
                if ($department) {
                    $q->whereHas('employee', fn ($employeeQuery) => $employeeQuery->where('department_id', $department->id));
                } else {
                    $q->whereRaw('1 = 0');
                }
            };

            $scope($statsQuery);
            $scope($query);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', LeaveRequest::STATUS_PENDING)->count(),
            'approved' => (clone $statsQuery)->where('status', LeaveRequest::STATUS_APPROVED)->count(),
            'rejected' => (clone $statsQuery)->where('status', LeaveRequest::STATUS_REJECTED)->count(),
        ];

        if ($request->filled('search')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('full_name', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaveRequests = $query->latest()->paginate(10)->withQueryString();

        return view('admin.leave-requests.index', compact('leaveRequests', 'stats'));
    }

    public function show(LeaveRequest $leaveRequest): View
    {
        $this->checkAccess(Auth::user(), $leaveRequest);

        $leaveRequest->load(['employee.department', 'employee.position', 'approver', 'histories.actor']);

        return view('admin.leave-requests.show', compact('leaveRequest'));
    }

    public function approve(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->checkAccess(Auth::user(), $leaveRequest);

        try {
            $this->service->approve($leaveRequest, (int) Auth::id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể phê duyệt đơn nghỉ phép.');
        }

        return back()->with('success', 'Phê duyệt đơn nghỉ phép thành công.');
    }

    public function reject(LeaveRequestRejectRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->checkAccess(Auth::user(), $leaveRequest);

        try {
            $this->service->reject($leaveRequest, (int) Auth::id(), $request->validated('reject_reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể từ chối đơn nghỉ phép.');
        }

        return back()->with('success', 'Từ chối đơn nghỉ phép thành công.');
    }

    private function checkAccess($user, LeaveRequest $leaveRequest): void
    {
        if (! in_array($user->role->name, ['admin', 'manager'], true)) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        if ($user->role->name === 'manager') {
            $employeeProfile = Employee::where('user_id', $user->id)->first();
            $department = $this->resolveManagedDepartment($employeeProfile);

            if (! $department || $leaveRequest->employee?->department_id !== $department->id) {
                abort(403, 'Bạn chỉ có quyền phê duyệt/từ chối đơn nghỉ phép của nhân viên trong phòng ban của mình.');
            }
        }
    }

    private function resolveManagedDepartment(?Employee $employeeProfile): ?Department
    {
        if (! $employeeProfile) {
            return null;
        }

        return Department::where('manager_id', $employeeProfile->id)->first()
            ?? Department::find($employeeProfile->department_id);
    }
}
