<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $employeeProfile = Employee::where('user_id', $user->id)->first();

        // 1. Build stats based on role scope (unaffected by filters)
        $statsQuery = LeaveRequest::query();
        if ($user->role->name === 'manager') {
            if ($employeeProfile) {
                $department = Department::where('manager_id', $employeeProfile->id)->first()
                    ?? Department::find($employeeProfile->department_id);
                if ($department) {
                    $statsQuery->whereHas('employee', function ($q) use ($department) {
                        $q->where('department_id', $department->id);
                    });
                } else {
                    $statsQuery->whereRaw('1 = 0');
                }
            } else {
                $statsQuery->whereRaw('1 = 0');
            }
        }

        $stats = [
            'total'    => (clone $statsQuery)->count(),
            'pending'  => (clone $statsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
        ];

        // 2. Query for listing
        $query = LeaveRequest::query()->with(['employee.department', 'approver']);

        // Enforce same scoping for listing
        if ($user->role->name === 'manager') {
            if ($employeeProfile) {
                $department = Department::where('manager_id', $employeeProfile->id)->first()
                    ?? Department::find($employeeProfile->department_id);
                if ($department) {
                    $query->whereHas('employee', function ($q) use ($department) {
                        $q->where('department_id', $department->id);
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Tìm kiếm theo tên nhân viên
        if ($request->filled('search')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%');
            });
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaveRequests = $query->latest()->paginate(10)->withQueryString();

        return view('admin.leave-requests.index', compact('leaveRequests', 'stats'));
    }

    public function show(LeaveRequest $leaveRequest): View
    {

        $user = Auth::user();

        if ($user->role->name === 'manager') {
            $this->checkAccess($user, $leaveRequest);
        }


        $leaveRequest->load([
            'employee.department',
            'employee.position',
            'approver',
        ]);

        return view('admin.leave-requests.show', compact('leaveRequest'));
    }

    public function approve(LeaveRequest $leaveRequest): RedirectResponse
    {
        $user = Auth::user();
        $this->checkAccess($user, $leaveRequest);

        if ($leaveRequest->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'Đơn nghỉ phép này đã được xử lý trước đó.');
        }

        $leaveRequest->update([
            'status'      => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Phê duyệt đơn nghỉ phép thành công.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $user = Auth::user();
        $this->checkAccess($user, $leaveRequest);

        if ($leaveRequest->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'Đơn nghỉ phép này đã được xử lý trước đó.');
        }

        $request->validate([
            'reject_reason' => 'required|string|max:1000',
        ], [
            'reject_reason.required' => 'Vui lòng nhập lý do từ chối.',
            'reject_reason.max'      => 'Lý do từ chối không được vượt quá 1000 ký tự.',
        ]);

        $leaveRequest->update([
            'status'        => 'rejected',
            'reject_reason' => $request->reject_reason,
            'approved_by'   => $user->id,
            'approved_at'   => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Từ chối đơn nghỉ phép thành công.');
    }

    private function checkAccess($user, LeaveRequest $leaveRequest): void
    {
        if ($user->role->name !== 'admin' && $user->role->name !== 'manager') {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        if ($user->role->name === 'manager') {
            $employeeProfile = Employee::where('user_id', $user->id)->first();
            if (!$employeeProfile) {
                abort(403, 'Tài khoản manager chưa liên kết hồ sơ nhân sự.');
            }
            $department = Department::where('manager_id', $employeeProfile->id)->first()
                ?? Department::find($employeeProfile->department_id);

            if (!$department || $leaveRequest->employee?->department_id !== $department->id) {
                abort(403, 'Bạn chỉ có quyền phê duyệt/từ chối đơn nghỉ phép của nhân viên trong phòng ban của mình.');
            }
        }
    }
}
