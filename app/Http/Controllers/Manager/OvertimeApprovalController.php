<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OvertimeApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $manager = Employee::where('user_id', Auth::id())->firstOrFail();
        $departmentId = $manager->department_id;

        $employees = Employee::query()
            ->where('department_id', $departmentId)
            ->orderBy('full_name')
            ->get();

        $overtimeRequests = OvertimeRequest::query()
            ->with(['employee.department'])
            ->whereHas('employee', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $keyword = trim((string) $request->search);
                $query->whereHas('employee', function ($q) use ($keyword) {
                    $q->where('full_name', 'like', '%' . $keyword . '%')
                        ->orWhere('employee_code', 'like', '%' . $keyword . '%');
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('work_date'), function ($query) use ($request) {
                $query->whereDate('work_date', $request->work_date);
            })
            ->when($request->filled('employee_id'), function ($query) use ($request) {
                $query->where('employee_id', $request->employee_id);
            })
            ->when($request->filled('department_id'), function ($query) use ($request) {
                $query->whereHas('employee', function ($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('manager.overtime-requests.index', [
            'overtimeRequests' => $overtimeRequests,
            'employees' => $employees,
            'filters' => $request->only(['search', 'status', 'work_date', 'employee_id', 'department_id']),
        ]);
    }

    public function show(OvertimeRequest $overtimeRequest): View
    {
        $manager = Employee::where('user_id', Auth::id())->firstOrFail();
        $this->authorizeInManagedDepartment($overtimeRequest, $manager->department_id);

        $overtimeRequest->load(['employee.department', 'approver']);

        return view('manager.overtime-requests.show', compact('overtimeRequest'));
    }

    public function approve(OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $manager = Employee::where('user_id', Auth::id())->firstOrFail();
        $this->authorizeInManagedDepartment($overtimeRequest, $manager->department_id);

        if ($overtimeRequest->status !== OvertimeRequest::STATUS_PENDING) {
            return back()->with('error', 'Chỉ đơn Pending mới được phê duyệt.');
        }

        $overtimeRequest->update([
            'status' => OvertimeRequest::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Phê duyệt đơn tăng ca thành công.');
    }

    public function reject(Request $request, OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $manager = Employee::where('user_id', Auth::id())->firstOrFail();
        $this->authorizeInManagedDepartment($overtimeRequest, $manager->department_id);

        if ($overtimeRequest->status !== OvertimeRequest::STATUS_PENDING) {
            return back()->with('error', 'Chỉ đơn Pending mới được từ chối.');
        }

        $validated = $request->validate([
            'reject_reason' => ['required', 'string', 'max:1000'],
        ], [
            'reject_reason.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        $overtimeRequest->update([
            'status' => OvertimeRequest::STATUS_REJECTED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'reject_reason' => $validated['reject_reason'],
        ]);

        return back()->with('success', 'Từ chối đơn tăng ca thành công.');
    }

    protected function authorizeInManagedDepartment(OvertimeRequest $overtimeRequest, ?int $departmentId): void
    {
        $requestDepartmentId = $overtimeRequest->employee?->department_id;

        if (! $departmentId || $requestDepartmentId !== $departmentId) {
            abort(403, 'Bạn không có quyền truy cập đơn tăng ca này.');
        }
    }
}
