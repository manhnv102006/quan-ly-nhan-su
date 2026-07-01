<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Support\DepartmentSummaryBuilder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
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
            'approver.employee',
            'rejecter.employee',
            'histories.actor.employee',
        ]);

        return view('admin.leave-requests.show', compact('leaveRequest'));
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
            ->with(['employee.department', 'approver.employee', 'rejecter.employee'])
            ->filter($filters)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return compact('leaveRequests', 'stats', 'filters');
    }
}
