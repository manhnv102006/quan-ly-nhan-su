<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use App\Services\AccountantReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected AccountantReportService $reports,
    ) {}

    public function index(): View
    {
        $reports = [
            [
                'title' => 'Chi phí lương theo phòng ban',
                'description' => 'Tổng hợp lương, phụ cấp, tăng ca, khấu trừ theo từng phòng ban',
                'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
                'href' => route('accountant.reports.salary-by-department'),
                'tone' => 'from-amber-500 to-orange-500',
            ],
            [
                'title' => 'Ngân sách dự kiến vs thực tế',
                'description' => 'So sánh chi phí lương thực chi với ngân sách từ hợp đồng',
                'icon' => 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5',
                'href' => route('accountant.reports.budget-comparison'),
                'tone' => 'from-indigo-500 to-violet-600',
            ],
            [
                'title' => 'Xuất báo cáo tài chính NS',
                'description' => 'Tổng hợp lương, BH, thuế — xuất CSV',
                'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75M9 16.5v.75m3-3v3M15 12v.75M3 4.5h15M4.5 19.5h15a1.5 1.5 0 001.5-1.5V6.75a1.5 1.5 0 00-1.5-1.5H4.5a1.5 1.5 0 00-1.5 1.5v11.25a1.5 1.5 0 001.5 1.5z',
                'href' => route('accountant.reports.financial'),
                'tone' => 'from-emerald-500 to-teal-500',
            ],
            [
                'title' => 'Bảng lương tháng',
                'description' => 'Quản lý kỳ lương & phiếu lương',
                'icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z',
                'href' => route('accountant.payroll-periods.index'),
                'tone' => 'from-rose-500 to-pink-500',
            ],
            [
                'title' => 'Báo cáo bảo hiểm',
                'description' => 'Đóng BHXH, BHYT, BHTN theo nhân viên',
                'icon' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
                'href' => route('accountant.insurance.reports'),
                'tone' => 'from-sky-500 to-indigo-500',
            ],
            [
                'title' => 'Báo cáo thuế TNCN',
                'description' => 'Khấu trừ thuế thu nhập cá nhân',
                'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75M9 16.5v.75m3-3v3M15 12v.75M3 4.5h15M4.5 19.5h15a1.5 1.5 0 001.5-1.5V6.75a1.5 1.5 0 00-1.5-1.5H4.5a1.5 1.5 0 00-1.5 1.5v11.25a1.5 1.5 0 001.5 1.5z',
                'href' => route('accountant.tax.declaration'),
                'tone' => 'from-violet-500 to-purple-600',
            ],
        ];

        return view('accountant.reports.index', compact('reports'));
    }

    public function salaryByDepartment(Request $request): View
    {
        $period = $this->resolvePeriod($request);
        $periods = $this->availablePeriods();
        $report = $this->reports->salaryCostByDepartment($period);

        return view('accountant.reports.salary-by-department', [
            'period' => $period,
            'periods' => $periods,
            'report' => $report,
            'year' => (int) $request->input('year', $period?->year ?? now()->year),
            'month' => (int) $request->input('month', $period?->month ?? now()->month),
        ]);
    }

    public function exportSalaryByDepartment(Request $request): Response
    {
        $period = $this->resolvePeriod($request);
        $report = $this->reports->salaryCostByDepartment($period);
        $label = $period ? "{$period->month}_{$period->year}" : 'chua_co_ky';

        return response($this->reports->salaryCostToCsv($report), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="chi_phi_luong_pb_'.$label.'.csv"',
        ]);
    }

    public function budgetComparison(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $report = $this->reports->budgetComparison($year, $month);

        return view('accountant.reports.budget-comparison', compact('report', 'year', 'month'));
    }

    public function exportBudgetComparison(Request $request): Response
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $report = $this->reports->budgetComparison($year, $month);

        return response($this->reports->budgetToCsv($report), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ngan_sach_'.$month.'_'.$year.'.csv"',
        ]);
    }

    public function financial(Request $request): View
    {
        $period = $this->resolvePeriod($request);
        $periods = $this->availablePeriods();
        $summary = $this->reports->financialSummary($period);
        $budget = $period
            ? $this->reports->budgetComparison((int) $period->year, (int) $period->month)
            : null;

        return view('accountant.reports.financial', [
            'period' => $period,
            'periods' => $periods,
            'summary' => $summary,
            'budget' => $budget,
            'year' => (int) $request->input('year', $period?->year ?? now()->year),
            'month' => (int) $request->input('month', $period?->month ?? now()->month),
        ]);
    }

    public function exportFinancial(Request $request): Response
    {
        $period = $this->resolvePeriod($request);
        $summary = $this->reports->financialSummary($period);
        $label = $period ? "{$period->month}_{$period->year}" : 'tong_hop';

        return response($this->reports->financialToCsv($summary), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="bao_cao_tai_chinh_ns_'.$label.'.csv"',
        ]);
    }

    protected function resolvePeriod(Request $request): ?PayrollPeriod
    {
        if ($request->filled('period_id')) {
            return PayrollPeriod::find($request->integer('period_id'));
        }

        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        return PayrollPeriod::query()
            ->where('year', $year)
            ->where('month', $month)
            ->first();
    }

    /**
     * @return \Illuminate\Support\Collection<int, PayrollPeriod>
     */
    protected function availablePeriods()
    {
        return PayrollPeriod::query()
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit(24)
            ->get();
    }
}
