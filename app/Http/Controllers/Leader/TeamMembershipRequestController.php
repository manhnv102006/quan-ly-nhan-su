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

        // á»¨ng viĂªn cĂ³ thá»ƒ Ä‘á» xuáº¥t thĂªm: cĂ¹ng phĂ²ng ban, chÆ°a thuá»™c nhĂ³m nĂ o.
        $addCandidates = Employee::query()
            ->where('department_id', $leader->department_id)
            ->where('id', '!=', $leader->id)
            ->where('status', 'active')
            ->whereDoesntHave('user', function ($q) {
                $q->whereHas('role', function ($role) {
                    $role->where('name', 'manager');
                });
            })
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code']);
        // ThĂ nh viĂªn hiá»‡n táº¡i cĂ³ thá»ƒ Ä‘á» xuáº¥t Ä‘Æ°a ra khá»i nhĂ³m.
        $removeCandidates = $this->scope->teamMembersQuery($leader)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code']);

        return view('leader.team-requests.index', compact('leader', 'requests', 'addCandidates', 'removeCandidates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());

        $validated = $request->validate([
            'action' => 'required|in:add,remove',
            'employee_id' => 'required|integer|exists:employees,id',
            'reason' => 'nullable|string|max:1000',
        ], [
            'action.required' => 'Vui lĂ²ng chá»n loáº¡i Ä‘á» xuáº¥t.',
            'employee_id.required' => 'Vui lĂ²ng chá»n nhĂ¢n viĂªn.',
            'employee_id.exists' => 'NhĂ¢n viĂªn khĂ´ng há»£p lá»‡.',
        ]);

        try {
            $this->service->create($leader, (int) Auth::id(), $validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('leader.team-requests.index')
            ->with('success', 'ÄĂ£ gá»­i Ä‘á» xuáº¥t, vui lĂ²ng chá» Quáº£n lĂ½ phĂª duyá»‡t.');
    }
}
