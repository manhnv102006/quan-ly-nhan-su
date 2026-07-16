<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Services\AccountantReportService;
use App\Services\AccountantStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AccountantStatsService $stats,
        private readonly AccountantReportService $reports,
    ) {}

    public function index(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $quarter = max(1, min(4, (int) $request->input('quarter', (int) ceil(now()->month / 3))));
        $costView = $request->input('cost_view', 'month') === 'quarter' ? 'quarter' : 'month';

        $data = $this->stats->dashboardStats($year, $month, $quarter, $costView);
        $activePeriod = $data['currentPeriod'];

        $salaryReport = $this->reports->salaryCostByDepartment($activePeriod);

        return view('accountant.dashboard', [
            ...$data,
            'financial' => $this->reports->financialSummary($activePeriod),
            'departmentBreakdown' => $this->stats->departmentPayrollBreakdown($salaryReport),
        ]);
    }
}
