<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRequestRejectRequest;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Services\LeaveApprovalService;
use App\Support\DepartmentSummaryBuilder;
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
        $this->authorize('viewAny', LeaveRequest::class);

        $data = $this->buildListData($request);

        return view('admin.leave-requests.index', [
            ...$data,
            'departmentSummaries' => DepartmentSummaryBuilder::forLeave(),
            'scopeLabel' => 'Toàn công ty',
            'showDepartmentColumn' => true,
            'selectedDepartment' => null,
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
        ]);

        return view('admin.leave-requests.show', compact('leaveRequest'));
    }

    public function approve(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('approve', $leaveRequest);

        try {
            $this->service->approve($leaveRequest, (int) Auth::id());
        } catch (ValidationException $e) {
            return redirect()
                ->route('admin.leave-requests.show', $leaveRequest)
                ->withErrors($e->errors())
                ->with('error', 'Không thể duyệt đơn nghỉ phép.');
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
            return redirect()
                ->route('admin.leave-requests.show', $leaveRequest)
                ->withErrors($e->errors())
                ->with('error', 'Không thể từ chối đơn nghỉ phép.');
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
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'status' => $request->input('status'),
            'leave_type' => $request->input('leave_type'),
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

        return compact('leaveRequests', 'stats', 'filters');
    }
}
