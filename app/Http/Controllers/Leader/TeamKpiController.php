<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leader\AllocateTeamKpiRequest;
use App\Http\Requests\Leader\SubmitKpiTeamReportRequest;
use App\Http\Requests\Leader\UpdateTeamKpiAllocationRequest;
use App\Models\EmployeeKPI;
use App\Models\KPIAssignment;
use App\Services\LeaderKpiService;
use App\Services\LeaderScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamKpiController extends Controller
{
    public function __construct(
        private readonly LeaderScopeService $scope,
        private readonly LeaderKpiService $kpi,
    ) {
    }

    public function index(Request $request): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $assignments = $this->kpi->teamAssignmentsQuery($leader)
            ->with('teamReport')
            ->paginate(10)
            ->withQueryString();

        return view('leader.team-kpis.index', compact('leader', 'assignments'));
    }

    public function show(Request $request, KPIAssignment $assignment): View
    {
        EmployeeKPI::markOverdueAsNotCompleted();

        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $this->kpi->assertOwnsAssignment($leader, $assignment);

        $assignment->load([
            'kpi.tasks',
            'assignedBy',
            'employeeKpis.employee',
            'teamReport',
        ]);

        $summary = $this->kpi->buildTeamSummary($assignment);
        $report = $this->kpi->syncReportDraft($assignment, $leader);

        return view('leader.team-kpis.show', compact('leader', 'assignment', 'summary', 'report'));
    }

    public function allocate(Request $request, KPIAssignment $assignment): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $this->kpi->assertOwnsAssignment($leader, $assignment);

        $assignment->load('kpi');
        $teamMembers = $this->scope->teamMembers($leader);

        return view('leader.team-kpis.allocate', compact('leader', 'assignment', 'teamMembers'));
    }

    public function storeAllocate(AllocateTeamKpiRequest $request, KPIAssignment $assignment): RedirectResponse
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());

        $this->kpi->allocateToMember(
            $request->user(),
            $leader,
            $assignment,
            $request->validated(),
        );

        return redirect()
            ->route('leader.team-kpis.show', $assignment)
            ->with('success', 'Đã phân bổ KPI cá nhân cho thành viên.');
    }

    public function editAllocation(Request $request, KPIAssignment $assignment, EmployeeKPI $employeeKpi): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $this->kpi->assertOwnsAssignment($leader, $assignment);
        $this->kpi->assertManagesEmployeeKpi($leader, $employeeKpi);

        $employeeKpi->load(['employee', 'kpi']);

        return view('leader.team-kpis.edit-allocation', compact('leader', 'assignment', 'employeeKpi'));
    }

    public function updateAllocation(UpdateTeamKpiAllocationRequest $request, KPIAssignment $assignment, EmployeeKPI $employeeKpi): RedirectResponse
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());

        $this->kpi->updateAllocation($leader, $assignment, $employeeKpi, $request->validated());

        return redirect()
            ->route('leader.team-kpis.show', $assignment)
            ->with('success', 'Đã cập nhật phân bổ KPI cá nhân.');
    }

    public function submitReport(SubmitKpiTeamReportRequest $request, KPIAssignment $assignment): RedirectResponse
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());

        $this->kpi->submitReport(
            $request->user(),
            $leader,
            $assignment,
            $request->string('summary')->toString(),
        );

        return redirect()
            ->route('leader.team-kpis.show', $assignment)
            ->with('success', 'Đã gửi báo cáo KPI nhóm lên Manager.');
    }
}
