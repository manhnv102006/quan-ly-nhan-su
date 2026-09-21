<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRequestRejectRequest;
use App\Models\Department;
use App\Models\EarlyLeaveRequest;
use App\Models\EarlyLeaveRequestHistory;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use App\Services\DepartmentLeaveCapacityService;
use App\Services\LeaveApprovalService;
use App\Support\DepartmentSummaryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function __construct(
        private readonly LeaveApprovalService $service,
        private readonly DepartmentLeaveCapacityService $departmentLeaveCapacity,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $data = $this->buildListData($request);

        return view('admin.leave-requests.index', [
            ...$data,
            'departmentSummaries' => DepartmentSummaryBuilder::forLeave(),
            'scopeLabel' => 'Toàn công ty',
            'showDepartmentColumn' => true,
            'selectedDepartment' => null,
            'recentHistories' => $data['recentHistories'],
        ]);
    }

    public function department(Request $request, Department $department): View
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $request->merge(['department_id' => $department->id]);

        return view('admin.leave-requests.department', [
            ...$this->buildListData($request, $department->id),
            'selectedDepartment' => $department,
            'scopeLabel' => $department->department_name,
            'showDepartmentColumn' => false,
        ]);
    }

    public function show(LeaveRequest $leaveRequest): View
    {
        $this->authorize('view', $leaveRequest);

        $leaveRequest->load([
            'employee.department',
            'employee.position',
            'employee.user',
            'approver.employee',
            'rejecter.employee',
            'histories.actor.employee',
            'document',
        ]);

        $capacityContext = $this->departmentLeaveCapacity->approvalCapacityContext($leaveRequest);
        $capacityEnforcement = (string) config('leave.department_capacity_enforcement', 'override');

        return view('admin.leave-requests.show', compact('leaveRequest', 'capacityContext', 'capacityEnforcement'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('approve', $leaveRequest);

        $validated = $request->validate([
            'capacity_override_reason' => ['nullable', 'string', 'max:1000'],
        ], [
            'capacity_override_reason.max' => 'Lý do duyệt vượt giới hạn không được vượt quá 1000 ký tự.',
        ]);

        try {
            $this->service->approve(
                $leaveRequest,
                (int) Auth::id(),
                null,
                $validated['capacity_override_reason'] ?? null,
            );
        } catch (ValidationException $e) {
            if (isset($e->errors()['capacity'])) {
                return redirect()
                    ->route('admin.leave-requests.show', $leaveRequest)
                    ->withErrors($e->errors());
            }

            $reason = collect($e->errors())->flatten()->first() ?? 'Lỗi không xác định.';

            return redirect()
                ->route('admin.leave-requests.show', $leaveRequest)
                ->withErrors($e->errors())
                ->with('error', 'Không thể duyệt đơn nghỉ phép: '.$reason);
        }

        return redirect()
            ->route('admin.leave-requests')
            ->with('success', 'Đã duyệt đơn nghỉ phép của quản lý.');
    }

    public function reject(LeaveRequestRejectRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('reject', $leaveRequest);

        try {
            $this->service->reject($leaveRequest, (int) Auth::id(), null, $request->validated('reject_reason'));
        } catch (ValidationException $e) {
            $reason = collect($e->errors())->flatten()->first() ?? 'Lỗi không xác định.';
            return redirect()
                ->route('admin.leave-requests.show', $leaveRequest)
                ->withErrors($e->errors())
                ->with('error', 'Không thể từ chối đơn nghỉ phép: ' . $reason);
        }

        return redirect()
            ->route('admin.leave-requests')
            ->with('success', 'Đã từ chối đơn nghỉ phép của quản lý.');
    }

    /**
     * @return array{leaveRequests: \Illuminate\Contracts\Pagination\LengthAwarePaginator, stats: array<string, int>, filters: array<string, mixed>}
     */
    private function buildListData(Request $request, ?int $departmentId = null): array
    {
        $request->validate([
            'employee_name' => ['nullable', 'string', 'max:100'],
            'employee_code' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', Rule::in(array_keys(LeaveRequest::STATUS_LABELS))],
            'leave_type' => ['nullable', Rule::in(array_keys(LeaveRequest::leaveTypeLabels()))],
            'start_from' => ['nullable', 'date'],
            'start_to' => ['nullable', 'date', 'after_or_equal:start_from'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ], [
            'start_to.after_or_equal' => 'Ngày kết thúc lọc phải sau hoặc bằng ngày bắt đầu.',
            'department_id.exists' => 'Phòng ban không hợp lệ.',
        ]);

        $filters = [
            'employee_name' => trim((string) $request->input('employee_name', '')),
            'employee_code' => trim((string) $request->input('employee_code', '')),
            'status' => $request->input('status'),
            'leave_type' => $request->input('leave_type'),
            'start_from' => $request->input('start_from'),
            'start_to' => $request->input('start_to'),
            'department_id' => $departmentId ?? $request->input('department_id'),
        ];

        $scopedQuery = LeaveRequest::query()
            ->when($filters['department_id'], function ($query) use ($filters) {
                $query->whereHas('employee', fn ($employeeQuery) => $employeeQuery->where('department_id', $filters['department_id']));
            });

        $stats = [
            'total' => (clone $scopedQuery)->count(),
            'pending' => (clone $scopedQuery)->where('status', LeaveRequest::STATUS_PENDING)->count(),
            'approved' => (clone $scopedQuery)->where('status', LeaveRequest::STATUS_APPROVED)->count(),
            'rejected' => (clone $scopedQuery)->where('status', LeaveRequest::STATUS_REJECTED)->count(),
        ];

        $leaveRequests = (clone $scopedQuery)
            ->with(['employee.department', 'employee.user', 'approver.employee', 'rejecter.employee'])
            ->filter($filters)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $departments = Department::query()
            ->orderBy('department_name')
            ->get(['id', 'department_name', 'department_code']);

        $recentHistories = LeaveRequestHistory::query()
            ->with(['actor', 'leaveRequest.employee'])
            ->latest()
            ->limit(20)
            ->get();

        return compact('leaveRequests', 'stats', 'filters', 'departments', 'recentHistories');
    }
}
