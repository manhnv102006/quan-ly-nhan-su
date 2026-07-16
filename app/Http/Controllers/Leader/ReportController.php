<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leader\SubmitLeaderTeamReportRequest;
use App\Models\LeaderTeamReport;
use App\Services\LeaderScopeService;
use App\Services\LeaderStatsService;
use App\Services\LeaderTeamReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly LeaderStatsService $stats,
        private readonly LeaderTeamReportService $teamReports,
        private readonly LeaderScopeService $scope,
    ) {
    }

    public function index(Request $request): View
    {
        $month = max(1, min(12, (int) $request->query('month', now()->month)));
        $year = max(2020, min(2100, (int) $request->query('year', now()->year)));

        $preview = $this->teamReports->buildPreview($request->user(), $month, $year);
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $history = $this->teamReports->submissionHistory($leader);

        return view('leader.reports.index', [
            'report' => $preview,
            'month' => $month,
            'year' => $year,
            'history' => $history,
        ]);
    }

    public function show(Request $request, LeaderTeamReport $leaderTeamReport): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());

        if ((int) $leaderTeamReport->leader_employee_id !== (int) $leader->id) {
            abort(403);
        }

        $reportData = $this->stats->teamReport(
            $request->user(),
            (int) $leaderTeamReport->period_month,
            (int) $leaderTeamReport->period_year,
        );

        return view('leader.reports.show', [
            'submission' => $leaderTeamReport,
            'report' => $reportData,
        ]);
    }

    public function submit(SubmitLeaderTeamReportRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->teamReports->submit(
            $request->user(),
            (int) $validated['period_month'],
            (int) $validated['period_year'],
            $validated['work_progress'],
            $validated['team_results'],
            $validated['notes'] ?? null,
        );

        return redirect()
            ->route('leader.reports.index', [
                'month' => $validated['period_month'],
                'year' => $validated['period_year'],
            ])
            ->with('success', 'Đã gửi báo cáo tiến độ và kết quả nhóm lên Manager.');
    }

    public function export(Request $request): Response
    {
        $month = max(1, min(12, (int) $request->query('month', now()->month)));
        $year = max(2020, min(2100, (int) $request->query('year', now()->year)));

        $report = $this->stats->teamReport($request->user(), $month, $year);
        $csv = "\xEF\xBB\xBF";
        $csv .= '"Báo cáo nhóm","Tháng '.$report['month'].'/'.$report['year'].'"'."\n\n";
        $csv .= '"Nhân viên","Phòng ban","KPI hoàn thành","KPI tổng","TB tiến độ","Ngày công","Đi muộn"'."\n";

        foreach ($report['rows'] as $row) {
            $csv .= implode(',', array_map(
                fn ($v) => '"'.str_replace('"', '""', (string) $v).'"',
                [
                    $row['employee']->full_name,
                    $row['employee']->department?->department_name ?? '—',
                    $row['kpi_completed'],
                    $row['kpi_total'],
                    $row['kpi_avg_progress'].'%',
                    $row['work_days'],
                    $row['late_days'],
                ]
            ))."\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="bao_cao_nhom_'.$report['month'].'_'.$report['year'].'.csv"',
        ]);
    }
}
