<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OvertimeApprovalController extends Controller
{
    private const PER_PAGE = 10;

    public function index(Request $request): View
    {
        $manager = $this->currentManager();
        $departmentId = $manager->department_id;
        $managedDepartment = Department::find($departmentId);

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
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('manager.overtime-requests.index', [
            'overtimeRequests' => $overtimeRequests,
            'employees' => $employees,
            'managedDepartment' => $managedDepartment,
            'filters' => $request->only(['search', 'status', 'work_date', 'employee_id', 'department_id']),
        ]);
    }

    public function show(OvertimeRequest $overtimeRequest): View
    {
        $this->authorize('view', $overtimeRequest);

        $overtimeRequest->load(['employee.department', 'approver', 'histories.actor']);

        return view('manager.overtime-requests.show', compact('overtimeRequest'));
    }

    public function approve(OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $this->authorize('approve', $overtimeRequest);

        if ($overtimeRequest->status !== OvertimeRequest::STATUS_PENDING) {
            return back()->with('error', 'Chỉ đơn Pending mới được phê duyệt.');
        }

        $this->processDecision(
            overtimeRequest: $overtimeRequest,
            status: OvertimeRequest::STATUS_APPROVED,
            action: 'approved',
            title: 'Đơn tăng ca đã được phê duyệt',
            content: 'Đơn tăng ca ngày '.$overtimeRequest->work_date?->format('d/m/Y').' của bạn đã được quản lý phê duyệt.',
        );

        return back()->with('success', 'Phê duyệt đơn tăng ca thành công.');
    }

    public function reject(Request $request, OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $this->authorize('reject', $overtimeRequest);

        if ($overtimeRequest->status !== OvertimeRequest::STATUS_PENDING) {
            return back()->with('error', 'Chỉ đơn Pending mới được từ chối.');
        }

        $validated = $request->validate([
            'reject_reason' => ['required', 'string', 'max:1000'],
        ], [
            'reject_reason.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        $this->processDecision(
            overtimeRequest: $overtimeRequest,
            status: OvertimeRequest::STATUS_REJECTED,
            action: 'rejected',
            title: 'Đơn tăng ca đã bị từ chối',
            content: 'Đơn tăng ca ngày '.$overtimeRequest->work_date?->format('d/m/Y').' của bạn đã bị từ chối. Lý do: '.$validated['reject_reason'],
            rejectReason: $validated['reject_reason'],
        );

        return back()->with('success', 'Từ chối đơn tăng ca thành công.');
    }

    protected function currentManager(): Employee
    {
        return Employee::where('user_id', Auth::id())->firstOrFail();
    }

    protected function processDecision(
        OvertimeRequest $overtimeRequest,
        string $status,
        string $action,
        string $title,
        string $content,
        ?string $rejectReason = null
    ): void {
        $actorId = (int) Auth::id();

        DB::transaction(function () use ($overtimeRequest, $status, $action, $title, $content, $rejectReason, $actorId) {
            $overtimeRequest->update([
                'status' => $status,
                'approved_by' => $actorId,
                'approved_at' => now(),
                'reject_reason' => $rejectReason,
            ]);

            OvertimeRequestHistory::create([
                'overtime_request_id' => $overtimeRequest->id,
                'actor_id' => $actorId,
                'action' => $action,
                'processed_at' => now(),
            ]);

            $this->notifyEmployee($overtimeRequest, $title, $content, $actorId);
        });
    }

    protected function notifyEmployee(OvertimeRequest $overtimeRequest, string $title, string $content, int $actorId): void
    {
        $employeeUserId = $overtimeRequest->employee?->user_id;
        if (! $employeeUserId) {
            return;
        }

        $notificationId = DB::table('notifications')->insertGetId([
            'title' => $title,
            'content' => $content,
            'sender_id' => $actorId,
            'type' => 'system',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notification_users')->insert([
            'notification_id' => $notificationId,
            'user_id' => $employeeUserId,
            'is_read' => false,
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
