<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\ReviewLeaderTeamReportRequest;
use App\Models\LeaderTeamReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LeaderTeamReportController extends Controller
{
    public function index(): View
    {
        $reports = LeaderTeamReport::query()
            ->with(['leaderEmployee.department'])
            ->where('manager_user_id', Auth::id())
            ->whereIn('status', [
                LeaderTeamReport::STATUS_SUBMITTED,
                LeaderTeamReport::STATUS_APPROVED,
                LeaderTeamReport::STATUS_REJECTED,
            ])
            ->latest('submitted_at')
            ->paginate(10);

        return view('manager.team-reports.index', compact('reports'));
    }

    public function show(LeaderTeamReport $report): View
    {
        abort_if((int) $report->manager_user_id !== (int) Auth::id(), 403);

        $report->load(['leaderEmployee.department', 'reviewedBy']);

        return view('manager.team-reports.show', compact('report'));
    }

    public function review(ReviewLeaderTeamReportRequest $request, LeaderTeamReport $report): RedirectResponse
    {
        $validated = $request->validated();

        $report->update([
            'status' => $validated['action'] === 'approve'
                ? LeaderTeamReport::STATUS_APPROVED
                : LeaderTeamReport::STATUS_REJECTED,
            'manager_review' => $validated['manager_review'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        $message = $validated['action'] === 'approve'
            ? 'Đã phê duyệt báo cáo nhóm.'
            : 'Đã từ chối báo cáo nhóm.';

        return redirect()
            ->route('manager.team-reports.show', $report)
            ->with('success', $message);
    }
}
