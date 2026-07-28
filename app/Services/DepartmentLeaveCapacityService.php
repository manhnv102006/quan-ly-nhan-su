<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Support\LeaveCapacityMessages;
use App\Support\LeaveDateRange;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DepartmentLeaveCapacityService
{
    /** Nhân viên: tối đa 30% phòng ban nghỉ cùng lúc (theo ngày). */
    public const RATIO_EMPLOYEE = 0.30;

    /** Quản lý & kế toán: tối đa 20% phòng ban nghỉ cùng lúc (theo ngày). */
    public const RATIO_MANAGER_ACCOUNTANT = 0.20;

    public function activeHeadcount(int $departmentId): int
    {
        return Employee::query()
            ->where('department_id', $departmentId)
            ->where('status', 'active')
            ->count();
    }

    public function maxConcurrentLeaveSlots(int $departmentId, float $ratio): int
    {
        $headcount = $this->activeHeadcount($departmentId);

        if ($headcount === 0) {
            return 0;
        }

        return (int) floor($headcount * $ratio);
    }

    public function submitBlockedMessage(
        Employee $applicant,
        Carbon|string $startDate,
        Carbon|string $endDate,
    ): ?string {
        $applicant->loadMissing('department');
        $departmentId = $applicant->department_id;
        if (! $departmentId) {
            return null;
        }

        return $this->firstQuotaViolationMessage(
            (int) $departmentId,
            $applicant,
            $startDate,
            $endDate,
            null,
            forApproval: false,
        );
    }

    public function approvalBlockedMessage(
        LeaveRequest $leaveRequest,
    ): ?string {
        $leaveRequest->loadMissing('employee.user.role', 'employee.department');
        $employee = $leaveRequest->employee;
        $departmentId = $employee?->department_id;

        if (! $departmentId || ! $employee) {
            return null;
        }

        return $this->firstQuotaViolationMessage(
            (int) $departmentId,
            $employee,
            $leaveRequest->start_date,
            $leaveRequest->end_date,
            $leaveRequest->id,
            forApproval: true,
            applicantDisplayName: $employee->full_name,
        );
    }

    private function firstQuotaViolationMessage(
        int $departmentId,
        Employee $applicant,
        Carbon|string $startDate,
        Carbon|string $endDate,
        ?int $ignoreLeaveRequestId,
        bool $forApproval,
        ?string $applicantDisplayName = null,
    ): ?string {
        $applicant->loadMissing('department');
        $departmentName = $applicant->department?->department_name ?? 'Phòng ban';

        $ratio = $applicant->leaveCapacityRatio();
        $percent = $applicant->leaveCapacityPercent();
        $roleLabel = $applicant->leaveCapacityRoleLabel();
        $maxSlots = $this->maxConcurrentLeaveSlots($departmentId, $ratio);
        $headcount = $this->activeHeadcount($departmentId);

        if ($headcount === 0) {
            return LeaveCapacityMessages::noActiveStaff($forApproval);
        }

        if ($maxSlots === 0) {
            return LeaveCapacityMessages::zeroSlots(
                $departmentName,
                $headcount,
                $percent,
                $roleLabel,
                $forApproval,
            );
        }

        $overlappingLeaves = $this->approvedOverlappingLeaves(
            $departmentId,
            $startDate,
            $endDate,
            $ignoreLeaveRequestId,
        );

        $periodLabel = LeaveDateRange::formatPeriod($startDate, $endDate);
        $fullDays = [];

        foreach (LeaveDateRange::eachCalendarDay($startDate, $endDate) as $day) {
            $approvedCount = $this->distinctApprovedEmployeesOnDay($overlappingLeaves, $day);

            if ($approvedCount >= $maxSlots) {
                $fullDays[] = ['day' => $day, 'count' => $approvedCount];
            }
        }

        if ($fullDays === []) {
            return null;
        }

        if ($forApproval) {
            return LeaveCapacityMessages::approvalBlocked(
                $applicantDisplayName ?? $applicant->full_name ?? 'Nhân viên',
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
