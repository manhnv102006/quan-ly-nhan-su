<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeKPI;
use App\Models\KPIAssignment;
use App\Models\KpiTeamReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LeaderKpiService
{
    public function __construct(
        private readonly LeaderScopeService $scope,
        private readonly NotificationService $notifications,
    ) {
    }

    /**
     * @return Builder<KPIAssignment>
     */
    public function teamAssignmentsQuery(Employee $leader): Builder
    {
        return KPIAssignment::query()
            ->where('leader_employee_id', $leader->id)
            ->where('status', 'active')
            ->with(['kpi', 'assignedBy'])
            ->withCount('employeeKpis')
            ->latest();
    }

    public function ownsAssignment(Employee $leader, KPIAssignment $assignment): bool
    {
        return (int) $assignment->leader_employee_id === (int) $leader->id;
    }

    public function assertOwnsAssignment(Employee $leader, KPIAssignment $assignment): void
    {
        if (! $this->ownsAssignment($leader, $assignment)) {
            abort(403, 'Bạn không có quyền thao tác KPI nhóm này.');
        }
    }

    public function assertManagesEmployeeKpi(Employee $leader, EmployeeKPI $employeeKpi): void
    {
        $employeeKpi->loadMissing(['kpiAssignment', 'employee']);

        if (! $employeeKpi->kpiAssignment || ! $this->ownsAssignment($leader, $employeeKpi->kpiAssignment)) {
            abort(403, 'Bạn không có quyền thao tác KPI cá nhân này.');
        }

        if (! $employeeKpi->employee || ! $this->scope->managesEmployee($leader, $employeeKpi->employee)) {
            abort(403, 'Nhân viên không thuộc nhóm của bạn.');
        }
    }

    public function allocateToMember(
        User $user,
        Employee $leader,
        KPIAssignment $assignment,
        array $data,
    ): EmployeeKPI {
        $this->assertOwnsAssignment($leader, $assignment);

        $employee = Employee::query()->findOrFail($data['employee_id']);

        if (! $this->scope->managesEmployee($leader, $employee)) {
            throw ValidationException::withMessages([
                'employee_id' => 'Nhân viên không thuộc nhóm của bạn.',
            ]);
        }

        $exists = $assignment->employeeKpis()
            ->where('employee_id', $employee->id)
            ->where('target', $data['target'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'target' => 'Mục tiêu này đã được giao cho nhân viên.',
            ]);
        }

        return $assignment->employeeKpis()->create([
            'kpi_id' => $assignment->kpi_id,
            'employee_id' => $employee->id,
            'target' => $data['target'],
            'comment' => $data['comment'] ?? null,
            'deadline' => $data['deadline'],
            'assigned_by' => $user->id,
            'progress' => 0,
            'status' => EmployeeKPI::STATUS_PENDING,
        ]);
    }

    public function updateAllocation(
        Employee $leader,
        KPIAssignment $assignment,
        EmployeeKPI $employeeKpi,
        array $data,
    ): EmployeeKPI {
        $this->assertOwnsAssignment($leader, $assignment);
        $this->assertManagesEmployeeKpi($leader, $employeeKpi);

        if ((int) $employeeKpi->assignment_id !== (int) $assignment->id) {
            abort(404);
        }

        $employeeKpi->update([
            'target' => $data['target'],
            'comment' => $data['comment'] ?? null,
            'deadline' => $data['deadline'],
        ]);

        return $employeeKpi->fresh(['employee', 'kpi']);
    }

    public function scoreMember(Employee $leader, EmployeeKPI $employeeKpi, array $data): EmployeeKPI
    {
        $this->assertManagesEmployeeKpi($leader, $employeeKpi);

        $employeeKpi->update([
            'leader_score' => $data['leader_score'],
            'leader_review' => $data['leader_review'] ?? null,
        ]);

        return $employeeKpi->fresh(['employee', 'kpi', 'kpiAssignment']);
    }

    /**
     * @return array{total_members: int, completed_count: int, avg_progress: float, avg_leader_score: ?float, member_rows: Collection}
     */
    public function buildTeamSummary(KPIAssignment $assignment): array
    {
        $employeeKpis = $assignment->employeeKpis()->with('employee')->get();

        $scored = $employeeKpis->whereNotNull('leader_score');

        return [
            'total_members' => $employeeKpis->count(),
            'completed_count' => $employeeKpis->where('status', EmployeeKPI::STATUS_COMPLETED)->count(),
            'avg_progress' => round((float) $employeeKpis->avg('progress'), 2),
            'avg_leader_score' => $scored->isNotEmpty()
                ? round((float) $scored->avg('leader_score'), 2)
                : null,
            'member_rows' => $employeeKpis,
        ];
    }

    public function syncReportDraft(KPIAssignment $assignment, Employee $leader): KpiTeamReport
    {
        $summary = $this->buildTeamSummary($assignment);

        return KpiTeamReport::updateOrCreate(
            ['assignment_id' => $assignment->id],
            [
                'leader_employee_id' => $leader->id,
                'total_members' => $summary['total_members'],
                'completed_count' => $summary['completed_count'],
                'avg_progress' => $summary['avg_progress'],
                'avg_leader_score' => $summary['avg_leader_score'],
                'status' => KpiTeamReport::STATUS_DRAFT,
            ],
        );
    }

    public function submitReport(
        User $user,
        Employee $leader,
        KPIAssignment $assignment,
        string $summary,
    ): KpiTeamReport {
        $this->assertOwnsAssignment($leader, $assignment);

        $teamSummary = $this->buildTeamSummary($assignment);

        if ($teamSummary['total_members'] === 0) {
            throw ValidationException::withMessages([
                'summary' => 'Chưa phân bổ KPI cá nhân cho thành viên. Vui lòng phân bổ trước khi gửi báo cáo.',
            ]);
        }

        $report = KpiTeamReport::updateOrCreate(
            ['assignment_id' => $assignment->id],
            [
                'leader_employee_id' => $leader->id,
                'summary' => trim($summary),
                'total_members' => $teamSummary['total_members'],
                'completed_count' => $teamSummary['completed_count'],
                'avg_progress' => $teamSummary['avg_progress'],
                'avg_leader_score' => $teamSummary['avg_leader_score'],
                'status' => KpiTeamReport::STATUS_SUBMITTED,
                'submitted_at' => now(),
            ],
        );

        if ($assignment->manager_id) {
            $this->notifications->sendToUser(
                (int) $assignment->manager_id,
                'Báo cáo KPI nhóm: '.$assignment->kpi_title,
                'Trưởng nhóm '.$leader->full_name.' đã gửi báo cáo kết quả KPI nhóm. Vui lòng xem và phê duyệt.',
                $user->id,
            );
        }

        return $report;
    }

    /**
     * @return Builder<EmployeeKPI>
     */
    public function teamMemberKpisQuery(Employee $leader, ?string $status = null): Builder
    {
        $teamIds = $this->scope->teamMemberIds($leader);

        return EmployeeKPI::query()
            ->with(['employee.department', 'kpi', 'kpiAssignment'])
            ->whereHas('kpiAssignment', fn (Builder $q) => $q->where('leader_employee_id', $leader->id))
            ->when($teamIds !== [], fn (Builder $q) => $q->whereIn('employee_id', $teamIds), fn (Builder $q) => $q->whereRaw('0 = 1'))
            ->when(in_array($status, ['pending', 'in_progress', 'completed', 'not_completed'], true), fn (Builder $q) => $q->where('status', $status))
            ->orderByDesc('updated_at');
    }
}
