<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\EmployeeKPI;
use App\Services\LeaderScopeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KPIController extends Controller
{
    public function __construct(private readonly LeaderScopeService $scope) {}

    public function index(Request $request): View
    {
        EmployeeKPI::markOverdueAsNotCompleted();

        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $teamIds = $this->scope->teamMemberIds($leader);
        $status = $request->query('status');

        $employeeKpis = EmployeeKPI::query()
            ->with(['employee.department', 'kpi'])
            ->when($teamIds !== [], fn ($q) => $q->whereIn('employee_id', $teamIds), fn ($q) => $q->whereRaw('0 = 1'))
            ->when(in_array($status, ['pending', 'in_progress', 'completed', 'not_completed'], true), fn ($q) => $q->where('status', $status))
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        $base = EmployeeKPI::query()->when($teamIds !== [], fn ($q) => $q->whereIn('employee_id', $teamIds), fn ($q) => $q->whereRaw('0 = 1'));

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
        $employeeKpi->load(['employee.department', 'kpi.tasks']);

        if (! $employeeKpi->employee || ! $this->scope->managesEmployee($leader, $employeeKpi->employee)) {
            abort(403, 'Bạn không có quyền xem KPI này.');
        }

        return view('leader.kpis.show', compact('leader', 'employeeKpi'));
    }
}
