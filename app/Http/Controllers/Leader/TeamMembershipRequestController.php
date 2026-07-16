<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TeamMembershipRequest;
use App\Services\LeaderScopeService;
use App\Services\TeamMembershipRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeamMembershipRequestController extends Controller
{
    public function __construct(
        private readonly LeaderScopeService $scope,
        private readonly TeamMembershipRequestService $service,
    ) {
    }

    public function index(Request $request): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());

        $requests = TeamMembershipRequest::query()
            ->where('leader_id', $leader->id)
            ->with(['employee', 'decidedBy'])
            ->latest()
            ->paginate(10);

        $pendingCount = TeamMembershipRequest::query()
            ->where('leader_id', $leader->id)
            ->where('status', TeamMembershipRequest::STATUS_PENDING)
            ->count();

        $addCandidates = Employee::query()
            ->where('department_id', $leader->department_id)
            ->where('id', '!=', $leader->id)
            ->where('status', 'active')
            ->whereNull('manager_id')
            ->whereDoesntHave('user', function ($q) {
                $q->whereHas('role', function ($role) {
                    $role->where('name', 'manager');
                });
            })
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code']);

        $removeCandidates = $this->scope->teamMembersQuery($leader)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code']);

        return view('leader.team-requests.index', compact(
            'leader',
            'requests',
            'addCandidates',
            'removeCandidates',
            'pendingCount',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());

        $validated = $request->validate([
            'action' => 'required|in:add,remove',
            'employee_id' => 'required|integer|exists:employees,id',
            'reason' => 'nullable|string|max:1000',
        ], [
            'action.required' => 'Vui lòng chọn loại đề xuất.',
            'employee_id.required' => 'Vui lòng chọn nhân viên.',
            'employee_id.exists' => 'Nhân viên không hợp lệ.',
        ]);

        try {
            $this->service->create($leader, (int) Auth::id(), $validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('leader.team-requests.index')
            ->with('success', 'Đã gửi đề xuất, vui lòng chờ Quản lý phê duyệt.');
    }
}