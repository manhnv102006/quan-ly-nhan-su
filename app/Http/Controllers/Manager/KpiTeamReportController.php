<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\ReviewKpiTeamReportRequest;
use App\Models\KpiTeamReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KpiTeamReportController extends Controller
{
    public function index(): View
    {
        $reports = KpiTeamReport::query()
            ->with(['assignment.kpi', 'leaderEmployee'])
            ->whereHas('assignment', fn ($q) => $q->where('manager_id', Auth::id()))
            ->whereIn('status', [
                KpiTeamReport::STATUS_SUBMITTED,
                KpiTeamReport::STATUS_APPROVED,
                KpiTeamReport::STATUS_REJECTED,
            ])
            ->latest('submitted_at')
            ->paginate(10);

        return view('manager.kpi-reports.index', compact('reports'));
    }

    public function show(KpiTeamReport $report): View
    {
        $report->load([
            'assignment.kpi.tasks',
            'assignment.employeeKpis.employee',
            'leaderEmployee',
            'reviewedBy',
        ]);

        abort_if($report->assignment?->manager_id !== Auth::id(), 403);

        return view('manager.kpi-reports.show', compact('report'));
    }

    public function review(ReviewKpiTeamReportRequest $request, KpiTeamReport $report): RedirectResponse
    {
        $validated = $request->validated();

        $report->update([
            'status' => $validated['action'] === 'approve'
                ? KpiTeamReport::STATUS_APPROVED
                : KpiTeamReport::STATUS_REJECTED,
            'manager_review' => $validated['manager_review'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        $message = $validated['action'] === 'approve'
            ? 'Đã phê duyệt báo cáo KPI nhóm.'
            : 'Đã từ chối báo cáo KPI nhóm.';

        return redirect()
            ->route('manager.kpi-reports.show', $report)
            ->with('success', $message);
    }
}
