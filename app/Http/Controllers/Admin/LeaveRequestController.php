<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'status' => $request->input('status'),
            'leave_type' => $request->input('leave_type'),
        ];

        $statsQuery = LeaveRequest::query();
        $query = LeaveRequest::query()
            ->with(['employee.department', 'approver.employee', 'rejecter.employee'])
            ->filter($filters);

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', LeaveRequest::STATUS_PENDING)->count(),
            'approved' => (clone $statsQuery)->where('status', LeaveRequest::STATUS_APPROVED)->count(),
            'rejected' => (clone $statsQuery)->where('status', LeaveRequest::STATUS_REJECTED)->count(),
        ];

        $leaveRequests = $query->latest()->paginate(10)->withQueryString();

        return view('admin.leave-requests.index', compact('leaveRequests', 'stats', 'filters'));
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
}
