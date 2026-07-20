<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\EmployeeKPI;
use App\Models\KpiTask;
use App\Models\User;
use Illuminate\Support\Collection;

class LeaderStatsService
{
    public function __construct(private readonly LeaderScopeService $scope) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboardStats(User $user): array
    {
        $leader = $this->scope->resolveLeaderEmployee($user);
        $teamIds = $leader ? $this->scope->teamMemberIds($leader) : [];

        $kpiQuery = EmployeeKPI::query()->when($teamIds !== [], fn ($q) => $q->whereIn('employee_id', $teamIds), fn ($q) => $q->whereRaw('0 = 1'));

        $kpiIds = (clone $kpiQuery)->pluck('kpi_id')->unique()->filter()->all();

        $todayPresent = $teamIds === [] ? 0 : Attendance::query()
            ->whereIn('employee_id', $teamIds)
            ->whereDate('attendance_date', today())
            ->whereIn('status', ['present', 'late'])
            ->count();

        $recentKpis = $teamIds === [] ? collect() : EmployeeKPI::query()
            ->with(['employee', 'kpi'])
            ->whereIn('employee_id', $teamIds)
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        return [
            'leader' => $leader,
            'teamCount' => count($teamIds),
            'kpiPending' => (clone $kpiQuery)->where('status', EmployeeKPI::STATUS_PENDING)->count(),
            'kpiInProgress' => (clone $kpiQuery)->where('status', EmployeeKPI::STATUS_IN_PROGRESS)->count(),
            'kpiCompleted' => (clone $kpiQuery)->where('status', EmployeeKPI::STATUS_COMPLETED)->count(),
            'taskCount' => $kpiIds === [] ? 0 : KpiTask::query()->whereIn('kpi_id', $kpiIds)->count(),
            'todayPresent' => $todayPresent,
            'recentKpis' => $recentKpis,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function teamReport(User $user): array
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($user);
        $teamIds = $this->scope->teamMemberIds($leader);

        $members = $this->scope->teamMembersQuery($leader)
            ->with(['department', 'position'])
            ->orderBy('full_name')
            ->get();

        $month = now()->month;
        $year = now()->year;

        $rows = $members->map(function ($employee) use ($month, $year) {
            $kpis = EmployeeKPI::query()
                ->with('kpi')
                ->where('employee_id', $employee->id)
                ->whereMonth('updated_at', $month)
                ->whereYear('updated_at', $year)
                ->get();

            $attendance = Attendance::query()
                ->where('employee_id', $employee->id)
                ->whereMonth('attendance_date', $month)
                ->whereYear('attendance_date', $year);

            return [
                'employee' => $employee,
                'kpi_total' => $kpis->count(),
                'kpi_completed' => $kpis->where('status', EmployeeKPI::STATUS_COMPLETED)->count(),
                'kpi_avg_progress' => round((float) $kpis->avg('progress'), 1),
                'work_days' => (clone $attendance)->whereIn('status', ['present', 'late'])->count(),
                'late_days' => (clone $attendance)->where('status', 'late')->count(),
            ];
        });

        return [
            'leader' => $leader,
            'rows' => $rows,
            'month' => $month,
            'year' => $year,
            'totals' => [
                'members' => $members->count(),
                'kpi_completed' => $rows->sum('kpi_completed'),
                'work_days' => $rows->sum('work_days'),
            ],
        ];
    }
}
