<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'role:admin']);
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $statsQuery = LeaveRequest::query();
        $query = LeaveRequest::query()->with(['employee.department', 'approver', 'rejecter']);

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', LeaveRequest::STATUS_PENDING)->count(),
            'approved' => (clone $statsQuery)->where('status', LeaveRequest::STATUS_APPROVED)->count(),
            'rejected' => (clone $statsQuery)->where('status', LeaveRequest::STATUS_REJECTED)->count(),
        ];

        if ($request->filled('search')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('full_name', 'like', '%'.$request->search.'%')
                    ->orWhere('employee_code', 'like', '%'.$request->search.'%');
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
        $this->authorize('view', $leaveRequest);

        $leaveRequest->load(['employee.department', 'employee.position', 'approver', 'rejecter', 'histories.actor']);

        return view('admin.leave-requests.show', compact('leaveRequest'));
    }
}
