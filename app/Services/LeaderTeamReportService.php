<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaderTeamReport;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LeaderTeamReportService
{
    public function __construct(
        private readonly LeaderScopeService $scope,
        private readonly LeaderStatsService $stats,
        private readonly NotificationService $notifications,
    ) {
    }

    public function resolveManagerUserId(Employee $leader): ?int
    {
        if (! $leader->department_id) {
            return null;
        }

        $department = Department::query()->find($leader->department_id);

        if (! $department?->manager_id) {
            return null;
        }

        $userId = Employee::query()->whereKey($department->manager_id)->value('user_id');

        return $userId ? (int) $userId : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildPreview(User $user, int $month, int $year): array
    {
        $reportData = $this->stats->teamReport($user, $month, $year);
        $leader = $reportData['leader'];

        $existing = $leader
            ? LeaderTeamReport::query()
                ->where('leader_employee_id', $leader->id)
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->first()
            : null;

        return array_merge($reportData, [
            'existing' => $existing,
            'manager_user_id' => $leader ? $this->resolveManagerUserId($leader) : null,
        ]);
    }

    public function submit(
        User $user,
        int $month,
        int $year,
        string $workProgress,
        string $teamResults,
        ?string $notes = null,
    ): LeaderTeamReport {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($user);
        $managerUserId = $this->resolveManagerUserId($leader);

        if (! $managerUserId) {
            throw ValidationException::withMessages([
                'work_progress' => 'Không tìm thấy Manager phòng ban để gửi báo cáo. Vui lòng liên hệ quản trị.',
            ]);
        }

        $preview = $this->stats->teamReport($user, $month, $year);
        $workProgress = trim($workProgress);
        $teamResults = trim($teamResults);

        if ($workProgress === '') {
            throw ValidationException::withMessages(['work_progress' => 'Vui lòng nhập tiến độ công việc.']);
        }

        if ($teamResults === '') {
            throw ValidationException::withMessages(['team_results' => 'Vui lòng nhập kết quả nhóm.']);
        }

        $existing = LeaderTeamReport::query()
            ->where('leader_employee_id', $leader->id)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->first();

        if ($existing?->isSubmitted()) {
            throw ValidationException::withMessages([
                'work_progress' => 'Báo cáo tháng '.$month.'/'.$year.' đã được gửi. Không thể gửi lại.',
            ]);
        }

        $rows = $preview['rows'];
        $kpiTotal = (int) $rows->sum('kpi_total');
        $kpiCompleted = (int) $rows->sum('kpi_completed');
        $avgProgress = $rows->isNotEmpty()
            ? round((float) $rows->avg('kpi_avg_progress'), 2)
            : 0;

        $report = LeaderTeamReport::updateOrCreate(
            [
                'leader_employee_id' => $leader->id,
                'period_month' => $month,
                'period_year' => $year,
            ],
            [
                'manager_user_id' => $managerUserId,
                'title' => 'Báo cáo nhóm tháng '.str_pad((string) $month, 2, '0', STR_PAD_LEFT).'/'.$year,
                'work_progress' => $workProgress,
                'team_results' => $teamResults,
                'notes' => $notes ? trim($notes) : null,
                'member_count' => (int) $preview['totals']['members'],
                'kpi_total' => $kpiTotal,
                'kpi_completed' => $kpiCompleted,
                'avg_kpi_progress' => $avgProgress,
                'total_work_days' => (int) $preview['totals']['work_days'],
                'total_late_days' => (int) $rows->sum('late_days'),
                'status' => LeaderTeamReport::STATUS_SUBMITTED,
                'submitted_at' => now(),
            ],
        );

        $this->notifications->sendToUser(
            $managerUserId,
            'Báo cáo tiến độ nhóm: '.$report->title,
            'Trưởng nhóm '.$leader->full_name.' đã gửi báo cáo tiến độ công việc và kết quả nhóm. Vui lòng xem và phê duyệt.',
            $user->id,
        );

        return $report;
    }

    /**
     * @return Collection<int, LeaderTeamReport>
     */
    public function submissionHistory(Employee $leader, int $limit = 12): Collection
    {
        return LeaderTeamReport::query()
            ->where('leader_employee_id', $leader->id)
            ->whereIn('status', [
                LeaderTeamReport::STATUS_SUBMITTED,
                LeaderTeamReport::STATUS_APPROVED,
                LeaderTeamReport::STATUS_REJECTED,
            ])
            ->latest('submitted_at')
            ->limit($limit)
            ->get();
    }
}
