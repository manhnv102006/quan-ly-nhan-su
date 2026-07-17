<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leader\UpdateLeaderEmployeeKpiScoreRequest;
use App\Models\EmployeeKPI;
use App\Services\LeaderKpiService;
use App\Services\LeaderScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KPIController extends Controller
{
    public function __construct(
        private readonly LeaderScopeService $scope,
        private readonly LeaderKpiService $kpi,
    ) {
    }

    public function index(Request $request): View
    {
        EmployeeKPI::markOverdueAsNotCompleted();

        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $status = $request->query('status');

        $employeeKpis = $this->kpi->teamMemberKpisQuery($leader, is_string($status) ? $status : null)
            ->paginate(15)
            ->withQueryString();

        $base = $this->kpi->teamMemberKpisQuery($leader);

        $stats = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', EmployeeKPI::STATUS_PENDING)->count(),
            'in_progress' => (clone $base)->where('status', EmployeeKPI::STATUS_IN_PROGRESS)->count(),
            'completed' => (clone $base)->where('status', EmployeeKPI::STATUS_COMPLETED)->count(),
        ];

        return view('leader.kpis.index', compact('leader', 'employeeKpis', 'stats', 'status'));
    }

    public function show(Request $request, EmployeeKPI $employeeKpi): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $this->kpi->assertManagesEmployeeKpi($leader, $employeeKpi);

        $employeeKpi->load(['employee.department', 'kpi.tasks', 'kpiAssignment.kpi']);

        return view('leader.kpis.show', compact('leader', 'employeeKpi'));
    }

    public function editScore(Request $request, EmployeeKPI $employeeKpi): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $this->kpi->assertManagesEmployeeKpi($leader, $employeeKpi);

        $employeeKpi->load(['employee', 'kpi', 'kpiAssignment']);   

        return view('leader.kpis.score', compact('leader', 'employeeKpi'));
    }

    public function updateScore(UpdateLeaderEmployeeKpiScoreRequest $request, EmployeeKPI $employeeKpi): RedirectResponse
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());

        $this->kpi->scoreMember($leader, $employeeKpi, $request->validated());

        return redirect()
            ->route('leader.kpis.show', $employeeKpi)
            ->with('success', 'Đã chấm điểm KPI cá nhân.');
    }
}
