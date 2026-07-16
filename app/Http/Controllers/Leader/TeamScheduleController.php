<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\EmployeeShift;
use App\Services\LeaderScopeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamScheduleController extends Controller
{
    public function __construct(private readonly LeaderScopeService $scope)
    {
    }

    public function index(Request $request): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());

        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->query('week_start'))->startOfDay()
            : Carbon::now()->startOfWeek();

        $weekEnd = (clone $weekStart)->addDays(6)->endOfDay();

        $teamMemberIds = $this->scope->teamMemberIds($leader);

        $shifts = EmployeeShift::query()
            ->whereIn('employee_id', $teamMemberIds)
            ->whereBetween('work_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->with(['employee', 'shift'])
            ->orderBy('work_date')
            ->get()
            ->groupBy(fn (EmployeeShift $shift) => $shift->work_date->toDateString());

        $days = collect(range(0, 6))->map(fn ($i) => (clone $weekStart)->addDays($i));

        return view('leader.team-schedule.index', compact('leader', 'shifts', 'days', 'weekStart'));
    }
}
