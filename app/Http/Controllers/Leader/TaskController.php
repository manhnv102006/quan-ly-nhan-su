<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\EmployeeKPI;
use App\Models\KpiTask;
use App\Services\LeaderScopeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(private readonly LeaderScopeService $scope) {}

    public function index(Request $request): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $teamIds = $this->scope->teamMemberIds($leader);
        $search = trim((string) $request->query('search', ''));

        $kpiIds = $teamIds === []
            ? []
            : EmployeeKPI::query()->whereIn('employee_id', $teamIds)->pluck('kpi_id')->unique()->filter()->all();

        $tasks = KpiTask::query()
            ->with(['kpi'])
            ->when($kpiIds !== [], fn ($q) => $q->whereIn('kpi_id', $kpiIds), fn ($q) => $q->whereRaw('0 = 1'))
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('kpi', fn ($kq) => $kq->where('title', 'like', "%{$search}%"));
            }))
            ->orderBy('kpi_id')
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        $employeeKpiMap = $teamIds === [] ? collect() : EmployeeKPI::query()
            ->with('employee')
            ->whereIn('employee_id', $teamIds)
            ->get()
            ->groupBy('kpi_id');

        return view('leader.tasks.index', compact('leader', 'tasks', 'employeeKpiMap', 'search'));
    }
}
