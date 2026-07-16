<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Concerns\ResolvesCurrentEmployee;
use App\Http\Controllers\Controller;
use App\Models\TeamMembershipRequest;
use App\Services\TeamMembershipRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeamMembershipRequestController extends Controller
{
    use ResolvesCurrentEmployee;

    public function __construct(private readonly TeamMembershipRequestService $service)
    {
    }

    public function index(Request $request): View
    {
        $manager = $this->currentManagerOrNull();

        if (! $manager) {
            return view('manager.team-requests.index', [
                'managerLinked' => false,
                'requests' => TeamMembershipRequest::query()->whereRaw('0 = 1')->paginate(10),
                'stats' => ['pending' => 0, 'approved' => 0, 'rejected' => 0],
            ]);
        }

        $status = $request->query('status');

        $scopedQuery = TeamMembershipRequest::query()->forManager($manager);

        $stats = [
            'pending' => (clone $scopedQuery)->where('status', TeamMembershipRequest::STATUS_PENDING)->count(),
            'approved' => (clone $scopedQuery)->where('status', TeamMembershipRequest::STATUS_APPROVED)->count(),
            'rejected' => (clone $scopedQuery)->where('status', TeamMembershipRequest::STATUS_REJECTED)->count(),
        ];

        $requests = (clone $scopedQuery)
            ->with(['leader', 'employee', 'decidedBy'])
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('manager.team-requests.index', [
            'managerLinked' => true,
            'requests' => $requests,
            'stats' => $stats,
            'status' => $status,
        ]);
    }

    public function approve(TeamMembershipRequest $teamMembershipRequest): RedirectResponse
    {
        $manager = $this->authorizeManagerAccess($teamMembershipRequest);

        try {
            $this->service->approve($teamMembershipRequest, (int) Auth::id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('manager.team-requests.index')
            ->with('success', 'ÄĂ£ duyá»‡t Ä‘á» xuáº¥t thĂ nh viĂªn nhĂ³m.');
    }

    public function reject(Request $request, TeamMembershipRequest $teamMembershipRequest): RedirectResponse
    {
        $manager = $this->authorizeManagerAccess($teamMembershipRequest);

        $validated = $request->validate([
            'decision_note' => 'required|string|max:1000',
        ], [
            'decision_note.required' => 'Vui lĂ²ng nháº­p lĂ½ do tá»« chá»‘i.',
        ]);

        try {
            $this->service->reject($teamMembershipRequest, (int) Auth::id(), $validated['decision_note']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('manager.team-requests.index')
            ->with('success', 'ÄĂ£ tá»« chá»‘i Ä‘á» xuáº¥t thĂ nh viĂªn nhĂ³m.');
    }

    private function authorizeManagerAccess(TeamMembershipRequest $teamMembershipRequest): \App\Models\Employee
    {
        $manager = $this->currentManager();

        $teamMembershipRequest->loadMissing('leader');

        $belongs = \App\Models\Employee::query()
            ->whereKey($teamMembershipRequest->leader_id)
            ->whereIn('department_id', \App\Models\Employee::departmentIdsForManagerApproval($manager))
            ->exists();

        if (! $belongs) {
            abort(403, 'Báº¡n khĂ´ng cĂ³ quyá»n xá»­ lĂ½ Ä‘á» xuáº¥t nĂ y.');
        }

        return $manager;
    }
}
