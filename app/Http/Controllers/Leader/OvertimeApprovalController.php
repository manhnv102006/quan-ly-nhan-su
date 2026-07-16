<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Http\Requests\OvertimeRequestRejectRequest;
use App\Models\OvertimeRequest;
use App\Services\LeaderScopeService;
use App\Services\LeaderTeamApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OvertimeApprovalController extends Controller
{
    public function __construct(
        private readonly LeaderScopeService $scope,
        private readonly LeaderTeamApprovalService $service,
    ) {
    }

    public function index(Request $request): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $filters = $request->only(['status', 'work_date_from', 'work_date_to']);

        $scopedQuery = OvertimeRequest::query()->forLeader($leader);

        $stats = [
            'awaiting_leader' => (clone $scopedQuery)->awaitingLeaderApproval($leader)->count(),
            'awaiting_manager' => (clone $scopedQuery)->awaitingManagerApproval()->count(),
            'approved' => (clone $scopedQuery)->where('status', OvertimeRequest::STATUS_APPROVED)->count(),
            'rejected' => (clone $scopedQuery)->where('status', OvertimeRequest::STATUS_REJECTED)->count(),
        ];

        $overtimeRequests = (clone $scopedQuery)
            ->with(['employee.department', 'employee.position', 'leaderApprover'])
            ->filter($filters)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('leader.overtime-requests.index', compact('leader', 'overtimeRequests', 'stats', 'filters'));
    }

    public function show(Request $request, OvertimeRequest $overtimeRequest): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $overtimeRequest->load(['employee.department', 'employee.position', 'leaderApprover', 'histories.actor']);

        if (! $overtimeRequest->employee || ! $this->scope->managesEmployee($leader, $overtimeRequest->employee)) {
            abort(403, 'Bạn chỉ được xem đơn tăng ca của thành viên trong nhóm.');
        }

        return view('leader.overtime-requests.show', compact('leader', 'overtimeRequest'));
    }

    public function approve(Request $request, OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());

        try {
            $this->service->approveOvertime($overtimeRequest, $leader, (int) Auth::id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể duyệt đơn.');
        }

        return back()->with('success', 'Đã duyệt bước 1. Đơn đã chuyển Quản lý phê duyệt.');
    }

    public function reject(OvertimeRequestRejectRequest $request, OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());

        try {
            $this->service->rejectOvertime($overtimeRequest, $leader, (int) Auth::id(), $request->validated('reject_reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể từ chối đơn.');
        }

        return redirect()
            ->route('leader.overtime-requests.index')
            ->with('success', 'Đã từ chối đơn tăng ca.');
    }
}
