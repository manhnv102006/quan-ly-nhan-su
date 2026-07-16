<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRequestRejectRequest;
use App\Models\LeaveRequest;
use App\Services\LeaderScopeService;
use App\Services\LeaderTeamApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LeaveApprovalController extends Controller
{
    public function __construct(
        private readonly LeaderScopeService $scope,
        private readonly LeaderTeamApprovalService $service,
    ) {
    }

    public function index(Request $request): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $filters = $request->only(['leave_type', 'status', 'start_from', 'start_to']);

        $scopedQuery = LeaveRequest::query()->forLeader($leader);

        $stats = [
            'awaiting_leader' => (clone $scopedQuery)->awaitingLeaderApproval($leader)->count(),
            'awaiting_manager' => (clone $scopedQuery)->awaitingManagerApproval()->count(),
            'approved' => (clone $scopedQuery)->where('status', LeaveRequest::STATUS_APPROVED)->count(),
            'rejected' => (clone $scopedQuery)->where('status', LeaveRequest::STATUS_REJECTED)->count(),
        ];

        $leaveRequests = (clone $scopedQuery)
            ->with(['employee.department', 'employee.position', 'leaderApprover'])
            ->filter($filters)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('leader.leave-requests.index', compact('leader', 'leaveRequests', 'stats', 'filters'));
    }

    public function show(Request $request, LeaveRequest $leaveRequest): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $leaveRequest->load(['employee.department', 'employee.position', 'leaderApprover', 'histories.actor']);

        if (! $leaveRequest->employee || ! $this->scope->managesEmployee($leader, $leaveRequest->employee)) {
            abort(403, 'Bạn chỉ được xem đơn nghỉ phép của thành viên trong nhóm.');
        }

        return view('leader.leave-requests.show', compact('leader', 'leaveRequest'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());

        try {
            $this->service->approveLeave($leaveRequest, $leader, (int) Auth::id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể duyệt đơn.');
        }

        return back()->with('success', 'Đã duyệt bước 1. Đơn đã chuyển Quản lý phê duyệt.');
    }

    public function reject(LeaveRequestRejectRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());

        try {
            $this->service->rejectLeave($leaveRequest, $leader, (int) Auth::id(), $request->validated('reject_reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể từ chối đơn.');
        }

        return redirect()
            ->route('leader.leave-requests.index')
            ->with('success', 'Đã từ chối đơn nghỉ phép.');
    }
}
