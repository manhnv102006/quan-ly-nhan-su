<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Support\LeaveCapacityMessages;
use App\Support\LeaveCapacityRules;
use App\Support\LeaveDateRange;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DepartmentLeaveCapacityService
{
    public function activeHeadcount(int $departmentId): int
    {
        return Employee::query()
            ->where('department_id', $departmentId)
            ->where('status', 'active')
            ->count();
    }

    public function maxConcurrentLeaveSlots(int $departmentId, float $ratio): int
    {
        return LeaveCapacityRules::slotsFor($this->activeHeadcount($departmentId), $ratio);
    }

    /**
     * Giữ khoá phòng ban để hai đơn cùng phòng không cùng lúc vượt hạn mức.
     * Chỉ có hiệu lực khi gọi trong transaction.
     */
    public function lockDepartment(?int $departmentId): void
    {
        if (! $departmentId) {
            return;
        }

        Department::query()->whereKey($departmentId)->lockForUpdate()->first();
    }

    /**
     * Nhân viên chỉ bị chặn gửi đơn khi hạn mức đã kín bởi các đơn ĐÃ DUYỆT.
     */
    public function submitBlockedMessage(
        Employee $applicant,
        Carbon|string $startDate,
        Carbon|string $endDate,
    ): ?string {
        if (! $applicant->department_id) {
            return null;
        }

        return $this->firstQuotaViolationMessage(
            (int) $applicant->department_id,
            $applicant,
            $startDate,
            $endDate,
            null,
            forApproval: false,
        );
    }

    public function approvalBlockedMessage(LeaveRequest $leaveRequest): ?string
    {
        $leaveRequest->loadMissing(['employee.user.role', 'employee.department']);
        $employee = $leaveRequest->employee;

        if (! $employee || ! $employee->department_id) {
            return null;
        }

        return $this->firstQuotaViolationMessage(
            (int) $employee->department_id,
            $employee,
            $leaveRequest->start_date,
            $leaveRequest->end_date,
            $leaveRequest->id,
            forApproval: true,
        );
    }

    private function firstQuotaViolationMessage(
        int $departmentId,
        Employee $applicant,
        Carbon|string $startDate,
        Carbon|string $endDate,
        ?int $ignoreLeaveRequestId,
        bool $forApproval,
    ): ?string {
        $applicant->loadMissing('department');
        $departmentName = $applicant->department?->department_name ?? 'Phòng ban';

        $ratio = LeaveCapacityRules::ratioFor($applicant);
        $percent = LeaveCapacityRules::toPercent($ratio);
        $roleLabel = LeaveCapacityRules::roleLabelFor($applicant);

        $headcount = $this->activeHeadcount($departmentId);
        $maxSlots = LeaveCapacityRules::slotsFor($headcount, $ratio);

        if ($headcount === 0) {
            return LeaveCapacityMessages::noActiveStaff($forApproval);
        }

        $overlappingLeaves = $this->approvedOverlappingLeaves(
            $departmentId,
            $startDate,
            $endDate,
            $ignoreLeaveRequestId,
        );

        $holidays = $this->holidaysInRange($startDate, $endDate);
        $fullDays = [];

        foreach (LeaveDateRange::eachCalendarDay($startDate, $endDate) as $day) {
            if ($this->isNonWorkingDay($day, $holidays)) {
                continue;
            }

            $approvedCount = $this->distinctApprovedEmployeesOnDay($overlappingLeaves, $day);

            if ($approvedCount >= $maxSlots) {
                $fullDays[] = ['day' => $day, 'count' => $approvedCount];
            }
        }

        if ($fullDays === []) {
            return null;
        }

        $periodLabel = LeaveDateRange::formatPeriod($startDate, $endDate);

        if ($forApproval) {
            return LeaveCapacityMessages::approvalBlocked(
                $applicant->full_name ?: 'Nhân viên',
                $departmentName,
                $periodLabel,
                $fullDays,
                $maxSlots,
                $percent,
                $roleLabel,
                $headcount,
            );
        }

        return LeaveCapacityMessages::employeeSubmitBlocked(
            $departmentName,
            $periodLabel,
            $fullDays,
            $maxSlots,
            $percent,
            $roleLabel,
            $headcount,
        );
    }

    /**
     * @return Collection<int, LeaveRequest>
     */
    private function approvedOverlappingLeaves(
        int $departmentId,
        Carbon|string $startDate,
        Carbon|string $endDate,
        ?int $ignoreLeaveRequestId,
    ): Collection {
        return LeaveRequest::query()
            ->forDepartment($departmentId)
            ->when($ignoreLeaveRequestId, fn ($q) => $q->where('id', '!=', $ignoreLeaveRequestId))
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->overlappingPeriod($startDate, $endDate)
            ->get();
    }

    /**
     * @return Collection<int, Holiday>
     */
    private function holidaysInRange(Carbon|string $startDate, Carbon|string $endDate): Collection
    {
        return Holiday::inRange(
            Carbon::parse($startDate)->toDateString(),
            Carbon::parse($endDate)->toDateString(),
        )->get();
    }

    /**
     * Chủ nhật và ngày Lễ không tính vào hạn mức (khớp cách tính total_days của đơn).
     *
     * @param  Collection<int, Holiday>  $holidays
     */
    private function isNonWorkingDay(Carbon $day, Collection $holidays): bool
    {
        if ($day->isSunday()) {
            return true;
        }

        return $holidays->contains(
            fn (Holiday $holiday) => $day->between($holiday->start_date, $holiday->end_date)
        );
    }

    /**
     * @param  Collection<int, LeaveRequest>  $overlappingLeaves
     */
    private function distinctApprovedEmployeesOnDay(Collection $overlappingLeaves, Carbon $day): int
    {
        return $overlappingLeaves
            ->filter(fn (LeaveRequest $leave) => $leave->coversCalendarDay($day))
            ->pluck('employee_id')
            ->unique()
            ->count();
    }
}
