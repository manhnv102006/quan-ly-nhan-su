<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
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
        $leaveRequest->loadMissing('employee.user.role');
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
        $ratio = $applicant->leaveCapacityRatio();
        $percent = $applicant->leaveCapacityPercent();
        $maxSlots = $this->maxConcurrentLeaveSlots($departmentId, $ratio);
        $headcount = $this->activeHeadcount($departmentId);

        if ($headcount === 0) {
            return $forApproval
                ? 'Phòng ban chưa có nhân viên đang làm việc; không thể xác định hạn mức nghỉ phép để duyệt đơn.'
                : 'Phòng ban chưa có nhân viên đang làm việc; không thể gửi đơn nghỉ phép.';
        }

        if ($maxSlots === 0) {
            return sprintf(
                'Phòng ban có %d nhân viên đang làm việc; hạn mức %d%% tương đương 0 người nghỉ/ngày (quy mô hiện tại).',
                $headcount,
                $percent,
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

        $dayLabels = collect($fullDays)
            ->map(fn (array $row) => $row['day']->format('d/m/Y'))
            ->join(', ');

        $sampleCount = $fullDays[0]['count'];

        if ($forApproval) {
            return sprintf(
                'Không thể duyệt đơn (khoảng %s): các ngày %s đã đủ hạn mức %d/%d nhân viên nghỉ phép (%d%% đối với vai trò của người nghỉ). Chỉ duyệt thêm được khi hết ngày nghỉ và nhân viên đi làm lại.',
                $periodLabel,
                $dayLabels,
                $sampleCount,
                $maxSlots,
                $percent,
            );
        }

        return sprintf(
            'Số lượng nghỉ phép đã giới hạn. Khoảng %s trùng các ngày %s (đã có %d/%d đơn được duyệt, hạn mức %d%% cho vai trò của bạn). Bạn không thể gửi đơn cho các ngày đã đủ hạn mức.',
            $periodLabel,
            $dayLabels,
            $sampleCount,
            $maxSlots,
            $percent,
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
