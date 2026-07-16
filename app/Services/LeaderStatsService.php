<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeKPI;
use App\Models\KpiTask;
use App\Models\TeamMembershipRequest;
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
        EmployeeKPI::markOverdueAsNotCompleted();

        $leader = $this->scope->resolveLeaderEmployee($user);
        $teamIds = $leader ? $this->scope->teamMemberIds($leader) : [];

        $memberQuery = $leader
            ? $this->scope->teamMembersQuery($leader)
            : Employee::query()->whereRaw('0 = 1');

        $kpiQuery = EmployeeKPI::query()
            ->when($teamIds !== [], fn ($q) => $q->whereIn('employee_id', $teamIds), fn ($q) => $q->whereRaw('0 = 1'));

        $kpiIds = (clone $kpiQuery)->pluck('kpi_id')->unique()->filter()->all();

        $month = now()->month;
        $year = now()->year;

        $attendanceTodayQuery = $teamIds === []
            ? Attendance::query()->whereRaw('0 = 1')
            : Attendance::query()
                ->whereIn('employee_id', $teamIds)
                ->whereDate('attendance_date', today());

        $todayPresent = (clone $attendanceTodayQuery)->where('status', 'present')->count();
        $todayLate = (clone $attendanceTodayQuery)->where('status', 'late')->count();
        $todayCheckedIn = $todayPresent + $todayLate;

        $monthlyWorkDays = $teamIds === [] ? 0 : Attendance::query()
            ->whereIn('employee_id', $teamIds)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->whereIn('status', ['present', 'late'])
            ->count();

        $monthlyLateDays = $teamIds === [] ? 0 : Attendance::query()
            ->whereIn('employee_id', $teamIds)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->where('status', 'late')
            ->count();

        $kpiPending = (clone $kpiQuery)->where('status', EmployeeKPI::STATUS_PENDING)->count();
        $kpiInProgress = (clone $kpiQuery)->where('status', EmployeeKPI::STATUS_IN_PROGRESS)->count();
        $kpiCompleted = (clone $kpiQuery)->where('status', EmployeeKPI::STATUS_COMPLETED)->count();
        $kpiNotCompleted = (clone $kpiQuery)->where('status', EmployeeKPI::STATUS_NOT_COMPLETED)->count();
        $kpiTotal = (clone $kpiQuery)->count();

        $recentKpis = $teamIds === [] ? collect() : EmployeeKPI::query()
            ->with(['employee', 'kpi'])
            ->whereIn('employee_id', $teamIds)
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $memberRows = $this->memberPerformanceRows($leader, $teamIds);

        $pendingTeamRequests = $leader
            ? TeamMembershipRequest::query()
                ->where('leader_id', $leader->id)
                ->where('status', TeamMembershipRequest::STATUS_PENDING)
                ->count()
            : 0;

        return [
            'leader' => $leader,
            'teamCount' => count($teamIds),
            'activeMembers' => (clone $memberQuery)->where('status', 'active')->count(),
            'inactiveMembers' => (clone $memberQuery)->whereIn('status', ['inactive', 'resigned'])->count(),
            'kpiTotal' => $kpiTotal,
            'kpiPending' => $kpiPending,
            'kpiInProgress' => $kpiInProgress,
            'kpiCompleted' => $kpiCompleted,
            'kpiNotCompleted' => $kpiNotCompleted,
            'avgKpiProgress' => round((float) (clone $kpiQuery)->avg('progress'), 1),
            'taskCount' => $kpiIds === [] ? 0 : KpiTask::query()->whereIn('kpi_id', $kpiIds)->count(),
            'todayPresent' => $todayPresent,
            'todayLate' => $todayLate,
            'todayCheckedIn' => $todayCheckedIn,
            'todayAbsent' => max(0, count($teamIds) - $todayCheckedIn),
            'monthlyWorkDays' => $monthlyWorkDays,
            'monthlyLateDays' => $monthlyLateDays,
            'month' => $month,
            'year' => $year,
            'pendingTeamRequests' => $pendingTeamRequests,
            'recentKpis' => $recentKpis,
            'memberRows' => $memberRows,
            'kpiStatusChart' => [
                ['label' => 'Chờ', 'value' => $kpiPending, 'color' => 'from-amber-400 to-orange-400', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700'],
                ['label' => 'Đang làm', 'value' => $kpiInProgress, 'color' => 'from-sky-400 to-indigo-400', 'bg' => 'bg-sky-50', 'text' => 'text-sky-700'],
                ['label' => 'Hoàn thành', 'value' => $kpiCompleted, 'color' => 'from-emerald-400 to-teal-400', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700'],
                ['label' => 'Không HT', 'value' => $kpiNotCompleted, 'color' => 'from-rose-400 to-red-400', 'bg' => 'bg-rose-50', 'text' => 'text-rose-700'],
            ],
        ];
    }

    /**
     * @param  list<int>  $teamIds
     * @return Collection<int, array<string, mixed>>
     */
    private function memberPerformanceRows(?Employee $leader, array $teamIds): Collection
    {
        if (! $leader || $teamIds === []) {
            return collect();
        }

        $checkedInToday = Attendance::query()
            ->whereIn('employee_id', $teamIds)
            ->whereDate('attendance_date', today())
            ->whereIn('status', ['present', 'late'])
            ->pluck('employee_id')
            ->flip();

        return $this->scope->teamMembersQuery($leader)
            ->with(['department'])
            ->orderBy('full_name')
            ->limit(8)
            ->get()
            ->map(function (Employee $employee) use ($checkedInToday) {
                $kpis = EmployeeKPI::query()->where('employee_id', $employee->id)->get();

                return [
                    'employee' => $employee,
                    'kpi_total' => $kpis->count(),
                    'kpi_completed' => $kpis->where('status', EmployeeKPI::STATUS_COMPLETED)->count(),
                    'kpi_avg_progress' => round((float) $kpis->avg('progress'), 1),
                    'present_today' => $checkedInToday->has($employee->id),
                ];
            });
    }

    /**
     * @return array<string, mixed>
     */
    public function teamReport(User $user, ?int $month = null, ?int $year = null): array
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($user);
        $teamIds = $this->scope->teamMemberIds($leader);

        $members = $this->scope->teamMembersQuery($leader)
            ->with(['department', 'position'])
            ->orderBy('full_name')
            ->get();

        $month ??= now()->month;
        $year ??= now()->year;

        $rows = $members->map(function ($employee) use ($month, $year) {
            $kpis = EmployeeKPI::query()
                ->with('kpi')
                ->where('employee_id', $employee->id)
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
                'kpi_total' => $rows->sum('kpi_total'),
                'kpi_completed' => $rows->sum('kpi_completed'),
                'kpi_avg_progress' => $rows->isNotEmpty() ? round((float) $rows->avg('kpi_avg_progress'), 1) : 0,
                'work_days' => $rows->sum('work_days'),
                'late_days' => $rows->sum('late_days'),
            ],
        ];
    }
}
