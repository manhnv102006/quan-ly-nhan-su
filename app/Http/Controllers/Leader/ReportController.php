<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Services\LeaderStatsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly LeaderStatsService $stats) {}

    public function index(Request $request): View
    {
        $report = $this->stats->teamReport($request->user());

        return view('leader.reports.index', compact('report'));
    }

    public function export(Request $request): Response
    {
        $report = $this->stats->teamReport($request->user());
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
