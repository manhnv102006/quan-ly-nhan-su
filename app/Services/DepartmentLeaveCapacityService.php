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
    public function workingHeadcount(int $departmentId, Carbon|string $day): int
    {
        return Employee::query()
            ->where('department_id', $departmentId)
            ->countsTowardDepartmentWorkingHeadcount($day)
            ->count();
    }

    public function maxConcurrentLeaveSlots(int $departmentId, float $ratio, Carbon|string|null $day = null): int
    {
        return LeaveCapacityRules::slotsFor(
            $this->workingHeadcount($departmentId, $day ?? today()),
            $ratio,
        );
    }

    /**
     * @deprecated Dùng workingHeadcount()
     */
    public function activeHeadcount(int $departmentId): int
    {
        return $this->workingHeadcount($departmentId, today());
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
        ?float $totalDays = null,
        ?string $leaveType = null,
    ): ?string {
        if ($leaveType !== null
            && $totalDays !== null
            && ! LeaveCapacityRules::countsTowardDepartmentCapacity($leaveType, $totalDays)) {
            return null;
        }

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
            leaveType: $leaveType,
        );
    }

    public function approvalBlockedMessage(LeaveRequest $leaveRequest): ?string
    {
        $context = $this->approvalCapacityContext($leaveRequest);

        if ($context === null || ! $context['blocked']) {
            return null;
        }

        return $context['message'];
    }

    /**
     * @return array{
     *     applies: bool,
     *     statutory_exempt: bool,
     *     blocked: bool,
     *     headcount: int,
     *     max_slots: int,
     *     percent: int,
     *     role_label: string,
     *     full_days: list<array{day: \Carbon\Carbon, count: int, headcount: int, max_slots: int}>,
     *     message: string|null,
     *     warning_message: string|null,
     * }|null
     */
    public function approvalCapacityContext(LeaveRequest $leaveRequest): ?array
    {
        $leaveRequest->loadMissing(['employee.user.role', 'employee.department']);
        $employee = $leaveRequest->employee;

        if (! $employee || ! $employee->department_id) {
            return null;
        }

        $statutoryExempt = LeaveCapacityRules::isStatutoryCapacityExemptLeaveType((string) $leaveRequest->leave_type);
        $countsTowardCapacity = LeaveCapacityRules::countsTowardDepartmentCapacity(
            (string) $leaveRequest->leave_type,
            (float) $leaveRequest->total_days,
        );

        if (! $countsTowardCapacity && ! $statutoryExempt) {
            return null;
        }

        return $this->buildCapacityContext(
            departmentId: (int) $employee->department_id,
            applicant: $employee,
            startDate: $leaveRequest->start_date,
            endDate: $leaveRequest->end_date,
            ignoreLeaveRequestId: $leaveRequest->id,
            forApproval: true,
            statutoryExempt: $statutoryExempt,
            countsTowardCapacity: $countsTowardCapacity,
        );
    }

    private function firstQuotaViolationMessage(
        int $departmentId,
        Employee $applicant,
        Carbon|string $startDate,
        Carbon|string $endDate,
        ?int $ignoreLeaveRequestId,
        bool $forApproval,
        ?string $leaveType = null,
    ): ?string {
        if ($leaveType !== null && LeaveCapacityRules::isStatutoryCapacityExemptLeaveType($leaveType)) {
            return null;
        }

        $context = $this->buildCapacityContext(
            departmentId: $departmentId,
            applicant: $applicant,
            startDate: $startDate,
            endDate: $endDate,
            ignoreLeaveRequestId: $ignoreLeaveRequestId,
            forApproval: $forApproval,
            statutoryExempt: false,
            countsTowardCapacity: true,
        );

        if ($context === null || $context['full_days'] === []) {
            return null;
        }

        return $context['message'];
    }

    /**
     * @return array{
     *     applies: bool,
     *     statutory_exempt: bool,
     *     blocked: bool,
     *     headcount: int,
     *     max_slots: int,
     *     percent: int,
     *     role_label: string,
     *     full_days: list<array{day: \Carbon\Carbon, count: int, headcount: int, max_slots: int}>,
     *     message: string|null,
     *     warning_message: string|null,
     * }|null
     */
    private function buildCapacityContext(
        int $departmentId,
        Employee $applicant,
        Carbon|string $startDate,
        Carbon|string $endDate,
        ?int $ignoreLeaveRequestId,
        bool $forApproval,
        bool $statutoryExempt,
        bool $countsTowardCapacity,
    ): ?array {
        $applicant->loadMissing('department');
        $departmentName = $applicant->department?->department_name ?? 'Phòng ban';

        $ratio = LeaveCapacityRules::ratioFor($applicant);
        $percent = LeaveCapacityRules::toPercent($ratio);
        $roleLabel = LeaveCapacityRules::roleLabelFor($applicant);

        $overlappingLeaves = $this->approvedOverlappingLeaves(
            $departmentId,
            $startDate,
            $endDate,
            $ignoreLeaveRequestId,
        );

        $holidays = $this->holidaysInRange($startDate, $endDate);
        $fullDays = [];
        $representativeHeadcount = 0;
        $representativeMaxSlots = 0;

        foreach (LeaveDateRange::eachCalendarDay($startDate, $endDate) as $day) {
            if ($this->isNonWorkingDay($day, $holidays)) {
                continue;
            }

            $headcount = $this->workingHeadcount($departmentId, $day);
            $maxSlots = LeaveCapacityRules::slotsFor($headcount, $ratio);
            $representativeHeadcount = max($representativeHeadcount, $headcount);
            $representativeMaxSlots = max($representativeMaxSlots, $maxSlots);

            if ($headcount === 0) {
                continue;
            }

            $approvedCount = $this->distinctApprovedEmployeesOnDay($overlappingLeaves, $day);

            if ($approvedCount >= $maxSlots) {
                $fullDays[] = [
                    'day' => $day,
                    'count' => $approvedCount,
                    'headcount' => $headcount,
                    'max_slots' => $maxSlots,
                ];
            }
        }

        if ($representativeHeadcount === 0) {
            $noStaffMessage = LeaveCapacityMessages::noActiveStaff($forApproval);

            return [
                'applies' => true,
                'statutory_exempt' => $statutoryExempt,
                'blocked' => $countsTowardCapacity,
                'headcount' => 0,
                'max_slots' => 0,
                'percent' => $percent,
                'role_label' => $roleLabel,
                'full_days' => [],
                'message' => $countsTowardCapacity ? $noStaffMessage : null,
                'warning_message' => $statutoryExempt ? $noStaffMessage : null,
            ];
        }

        $periodLabel = LeaveDateRange::formatPeriod($startDate, $endDate);
        $blocked = $fullDays !== [] && $countsTowardCapacity;

        $message = null;
        $warningMessage = null;

        if ($fullDays !== []) {
            if ($forApproval) {
                if ($statutoryExempt) {
                    $warningMessage = LeaveCapacityMessages::statutoryCapacityWarning(
                        $applicant->full_name ?: 'Nhân viên',
                        $departmentName,
                        $periodLabel,
                        $fullDays,
                        $representativeMaxSlots,
                        $percent,
                        $roleLabel,
                        $representativeHeadcount,
                    );
                } elseif ($blocked) {
                    $message = LeaveCapacityMessages::approvalBlocked(
                        $applicant->full_name ?: 'Nhân viên',
                        $departmentName,
                        $periodLabel,
                        $fullDays,
                        $representativeMaxSlots,
                        $percent,
                        $roleLabel,
                        $representativeHeadcount,
                    );
                }
            } else {
                $message = LeaveCapacityMessages::employeeSubmitBlocked(
                    $departmentName,
                    $periodLabel,
                    $fullDays,
                    $representativeMaxSlots,
                    $percent,
                    $roleLabel,
                    $representativeHeadcount,
                );
            }
        }

        if ($fullDays === [] && ! $statutoryExempt && ! $countsTowardCapacity) {
            return null;
        }

        return [
            'applies' => true,
            'statutory_exempt' => $statutoryExempt,
            'blocked' => $blocked,
            'headcount' => $representativeHeadcount,
            'max_slots' => $representativeMaxSlots,
            'percent' => $percent,
            'role_label' => $roleLabel,
            'full_days' => $fullDays,
            'message' => $message,
            'warning_message' => $warningMessage,
        ];
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
            ->where('total_days', '<', LeaveCapacityRules::LONG_LEAVE_EXEMPT_FROM_DAYS)
            ->whereNotIn('leave_type', LeaveCapacityRules::STATUTORY_CAPACITY_EXEMPT_LEAVE_TYPES)
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
