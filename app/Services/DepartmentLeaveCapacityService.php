<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Support\LeaveDateRange;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DepartmentLeaveCapacityService
{
    /** Tỷ lệ tối đa nhân viên cùng nghỉ phép trong một phòng ban (theo ngày). */
    public const MAX_LEAVE_RATIO = 0.30;

    public function activeHeadcount(int $departmentId): int
    {
        return Employee::query()
            ->where('department_id', $departmentId)
            ->where('status', 'active')
            ->count();
    }

    /**
     * Số người tối đa được nghỉ cùng lúc trong phòng ban (làm tròn xuống).
     * Ví dụ: 10 nhân viên → tối đa 3 người/ngày.
     */
    public function maxConcurrentLeaveSlots(int $departmentId): int
    {
        $headcount = $this->activeHeadcount($departmentId);

        if ($headcount === 0) {
            return 0;
        }

        return (int) floor($headcount * self::MAX_LEAVE_RATIO);
    }

    /**
     * Chặn gửi đơn mới khi số đơn đã duyệt đã đạt 30%/ngày.
     * Đơn chờ duyệt không tính vào hạn mức — nhân viên vẫn gửi thêm đơn được.
     */
    public function submitBlockedMessage(
        int $departmentId,
        Carbon|string $startDate,
        Carbon|string $endDate,
    ): ?string {
        return $this->firstQuotaViolationMessage(
            $departmentId,
            $startDate,
            $endDate,
            null,
            forApproval: false,
        );
    }

    /**
     * Chặn duyệt thêm khi hạn mức 30%/ngày đã đủ (chỉ tính đơn đã duyệt).
     */
    public function approvalBlockedMessage(
        int $departmentId,
        Carbon|string $startDate,
        Carbon|string $endDate,
        int $leaveRequestId,
    ): ?string {
        return $this->firstQuotaViolationMessage(
            $departmentId,
            $startDate,
            $endDate,
            $leaveRequestId,
            forApproval: true,
        );
    }

    private function firstQuotaViolationMessage(
        int $departmentId,
        Carbon|string $startDate,
        Carbon|string $endDate,
        ?int $ignoreLeaveRequestId,
        bool $forApproval,
    ): ?string {
        $maxSlots = $this->maxConcurrentLeaveSlots($departmentId);
        $headcount = $this->activeHeadcount($departmentId);

        if ($headcount === 0) {
            return $forApproval
                ? 'Phòng ban chưa có nhân viên đang làm việc; không thể xác định hạn mức nghỉ phép để duyệt đơn.'
                : 'Phòng ban chưa có nhân viên đang làm việc; không thể gửi đơn nghỉ phép.';
        }

        if ($maxSlots === 0) {
            return sprintf(
                'Phòng ban có %d nhân viên đang làm việc; hạn mức 30%% tương đương 0 người nghỉ/ngày (quy mô hiện tại).',
                $headcount,
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
                'Không thể duyệt đơn (khoảng %s): các ngày %s đã đủ hạn mức %d/%d nhân viên nghỉ phép (30%%). Các ngày này trùng khoảng với đơn đã duyệt khác — chỉ duyệt thêm được khi hết ngày nghỉ và nhân viên đi làm lại.',
                $periodLabel,
                $dayLabels,
                $sampleCount,
                $maxSlots,
            );
        }

        return sprintf(
            'Số lượng nghỉ phép đã giới hạn. Khoảng %s trùng các ngày %s (đã có %d/%d đơn được duyệt). Bạn không thể gửi đơn cho các ngày đã đủ hạn mức; vui lòng chọn ngày khác hoặc sau khi nhân viên đi làm lại.',
            $periodLabel,
            $dayLabels,
            $sampleCount,
            $maxSlots,
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
