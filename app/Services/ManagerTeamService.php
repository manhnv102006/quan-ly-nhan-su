<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ManagerTeamService
{
    public function __construct(
        private readonly ManagerScopeService $scope,
        private readonly NotificationService $notifications,
    ) {
    }

    public function assertOwnsTeam(Employee $manager, Team $team): void
    {
        $departmentId = $this->scope->managedDepartmentId($manager);

        if (! $departmentId || (int) $team->department_id !== $departmentId) {
            abort(403, 'Bạn không có quyền thao tác nhóm này.');
        }
    }

    public function createTeam(User $user, Employee $manager, array $data): Team
    {
        $departmentId = $this->scope->managedDepartmentId($manager);

        if (! $departmentId) {
            abort(403, 'Chưa xác định phòng ban quản lý.');
        }

        if (! empty($data['leader_employee_id'])) {
            $this->assertValidLeader($manager, (int) $data['leader_employee_id']);
            $this->assertLeaderNotInOtherTeam((int) $data['leader_employee_id']);
        }

        $team = Team::create([
            'department_id' => $departmentId,
            'name' => trim($data['name']),
            'description' => isset($data['description']) ? trim($data['description']) : null,
            'leader_employee_id' => $data['leader_employee_id'] ?? null,
            'created_by' => $user->id,
            'status' => Team::STATUS_ACTIVE,
        ]);

        if ($team->leader_employee_id) {
            $this->notifyLeaderAssigned($team, $user);
        }

        return $team;
    }

    public function updateTeam(User $user, Employee $manager, Team $team, array $data): Team
    {
        $this->assertOwnsTeam($manager, $team);

        $oldLeaderId = $team->leader_employee_id;
        $newLeaderId = $data['leader_employee_id'] ?? null;

        if ($newLeaderId) {
            $this->assertValidLeader($manager, (int) $newLeaderId);
            if ((int) $newLeaderId !== (int) $oldLeaderId) {
                $this->assertLeaderNotInOtherTeam((int) $newLeaderId, $team->id);
            }
        }

        $team->update([
            'name' => trim($data['name']),
            'description' => isset($data['description']) ? trim($data['description']) : null,
            'leader_employee_id' => $newLeaderId,
            'status' => $data['status'] ?? $team->status,
        ]);

        if ($newLeaderId && (int) $newLeaderId !== (int) $oldLeaderId) {
            Employee::query()
                ->where('manager_id', $oldLeaderId)
                ->update(['manager_id' => $newLeaderId]);

            $this->notifyLeaderAssigned($team->fresh(), $user);
        }

        if (! $newLeaderId && $oldLeaderId) {
            Employee::query()
                ->where('manager_id', $oldLeaderId)
                ->update(['manager_id' => null]);
        }

        return $team->fresh(['leader', 'department']);
    }

    public function deleteTeam(Employee $manager, Team $team): void
    {
        $this->assertOwnsTeam($manager, $team);

        if ($team->leader_employee_id) {
            Employee::query()
                ->where('manager_id', $team->leader_employee_id)
                ->update(['manager_id' => null]);
        }

        $team->delete();
    }

    /**
     * @param  list<int>  $employeeIds
     */
    public function assignMembers(Employee $manager, Team $team, array $employeeIds): int
    {
        $this->assertOwnsTeam($manager, $team);

        if (! $team->leader_employee_id) {
            throw ValidationException::withMessages([
                'employee_ids' => 'Vui lòng chọn Trưởng nhóm trước khi gán thành viên.',
            ]);
        }

        $assigned = 0;

        foreach ($employeeIds as $employeeId) {
            $employee = Employee::query()->find($employeeId);

            if (! $employee || ! $this->scope->managesEmployee($manager, $employee)) {
                continue;
            }

            if ((int) $employee->id === (int) $team->leader_employee_id) {
                continue;
            }

            if ($this->isInAnyTeam($employee)) {
                $otherTeam = $this->teamForEmployee($employee);
                $teamName = $otherTeam?->name ?? 'nhóm khác';

                throw ValidationException::withMessages([
                    'employee_ids' => $employee->full_name.' đang thuộc '.$teamName.'. Hãy gỡ khỏi nhóm cũ trước.',
                ]);
            }

            if ((int) $employee->manager_id !== (int) $team->leader_employee_id) {
                $employee->update(['manager_id' => $team->leader_employee_id]);
                $assigned++;
            }
        }

        return $assigned;
    }

    public function removeMember(Employee $manager, Team $team, Employee $member): void
    {
        $this->assertOwnsTeam($manager, $team);

        if (! $team->leader_employee_id || (int) $member->manager_id !== (int) $team->leader_employee_id) {
            abort(403, 'Nhân viên không thuộc nhóm này.');
        }

        if (! $this->scope->managesEmployee($manager, $member)) {
            abort(403);
        }

        $member->update(['manager_id' => null]);
    }

    /**
     * @return Collection<int, Employee>
     */
    public function availableLeaders(Employee $manager): Collection
    {
        return $this->scope->managedEmployeesQuery($manager)
            ->where('status', 'active')
            ->whereHas('user.role', fn ($q) => $q->where('name', 'leader'))
            ->orderBy('full_name')
            ->get();
    }

    /**
     * NV có thể thêm vào nhóm (chưa thuộc nhóm khác).
     *
     * @return Collection<int, Employee>
     */
    public function candidatesToAdd(Employee $manager, Team $team): Collection
    {
        $memberIds = $this->members($team)->pluck('id');
        $otherTeamLeaderIds = $this->otherTeamLeaderIds($team);

        return $this->scope->managedEmployeesQuery($manager)
            ->with(['position', 'user.role'])
            ->where('status', 'active')
            ->where('id', '!=', $manager->id)
            ->when($team->leader_employee_id, fn ($q) => $q->where('id', '!=', $team->leader_employee_id))
            ->whereNotIn('id', $memberIds)
            ->where(function ($q) use ($otherTeamLeaderIds) {
                $q->whereNull('manager_id');

                if ($otherTeamLeaderIds !== []) {
                    // Cho phép NV có manager_id cũ (vd. trỏ tới trưởng phòng) chưa thuộc nhóm thật
                    $q->orWhereNotIn('manager_id', $otherTeamLeaderIds);
                } else {
                    $q->orWhereNotNull('manager_id');
                }
            })
            ->orderBy('full_name')
            ->get();
    }

    /**
     * @return list<int>
     */
    private function otherTeamLeaderIds(Team $team): array
    {
        return Team::query()
            ->where('department_id', $team->department_id)
            ->whereNotNull('leader_employee_id')
            ->when($team->leader_employee_id, fn ($q) => $q->where('leader_employee_id', '!=', $team->leader_employee_id))
            ->pluck('leader_employee_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function isInAnyTeam(Employee $employee, ?int $exceptTeamId = null): bool
    {
        if (! $employee->manager_id) {
            return false;
        }

        $query = Team::query()->where('leader_employee_id', $employee->manager_id);

        if ($exceptTeamId) {
            $query->where('id', '!=', $exceptTeamId);
        }

        return $query->exists();
    }

    public function teamForEmployee(Employee $employee): ?Team
    {
        if (! $employee->manager_id) {
            return null;
        }

        return Team::query()
            ->where('leader_employee_id', $employee->manager_id)
            ->first();
    }

    /**
     * @return Collection<int, Employee>
     */
    public function members(Team $team): Collection
    {
        if (! $team->leader_employee_id) {
            return collect();
        }

        return Employee::query()
            ->with(['position', 'user'])
            ->where('manager_id', $team->leader_employee_id)
            ->orderBy('full_name')
            ->get();
    }

    /**
     * @deprecated Use candidatesToAdd()
     *
     * @return Collection<int, Employee>
     */
    public function assignableMembers(Employee $manager, Team $team): Collection
    {
        return $this->candidatesToAdd($manager, $team);
    }

    private function assertValidLeader(Employee $manager, int $leaderEmployeeId): void
    {
        $leader = Employee::query()->with('user.role')->find($leaderEmployeeId);

        if (! $leader || ! $this->scope->managesEmployee($manager, $leader)) {
            throw ValidationException::withMessages(['leader_employee_id' => 'Trưởng nhóm không hợp lệ.']);
        }

        if (! $leader->user?->isLeader()) {
            throw ValidationException::withMessages(['leader_employee_id' => 'Nhân viên được chọn phải có vai trò Trưởng nhóm.']);
        }
    }

    private function assertLeaderNotInOtherTeam(int $leaderEmployeeId, ?int $exceptTeamId = null): void
    {
        $exists = Team::query()
            ->where('leader_employee_id', $leaderEmployeeId)
            ->when($exceptTeamId, fn ($q) => $q->where('id', '!=', $exceptTeamId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['leader_employee_id' => 'Trưởng nhóm này đã được gán cho nhóm khác.']);
        }
    }

    private function notifyLeaderAssigned(Team $team, User $sender): void
    {
        $leader = $team->leader;

        if ($leader?->user_id) {
            $this->notifications->sendToUser(
                (int) $leader->user_id,
                'Bạn được giao làm Trưởng nhóm: '.$team->name,
                'Manager đã tạo/giao nhóm '.$team->name.' cho bạn. Vui lòng quản lý thành viên và điều phối công việc.',
                $sender->id,
            );
        }
    }
}
