<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
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
     * Kiểm tra từng ngày trong khoảng nghỉ: nếu đã đủ quota thì trả về thông báo lỗi.
     */
    public function capacityErrorForDateRange(
        int $departmentId,
        Carbon|string $startDate,
        Carbon|string $endDate,
        ?int $ignoreLeaveRequestId = null,
    ): ?string {
        $maxSlots = $this->maxConcurrentLeaveSlots($departmentId);
        $headcount = $this->activeHeadcount($departmentId);

        if ($headcount === 0) {
            return 'Phòng ban chưa có nhân viên đang làm việc để áp dụng quy định nghỉ phép.';
        }

        if ($maxSlots === 0) {
            return sprintf(
                'Phòng ban có %d nhân viên; quy định chỉ cho phép nghỉ tối đa 30%% cùng lúc (0 người/ngày với quy mô hiện tại).',
                $headcount,
            );
        }

        $overlappingLeaves = $this->approvedOverlappingLeaves(
            $departmentId,
            $startDate,
            $endDate,
            $ignoreLeaveRequestId,
        );

        if ($overlappingLeaves->isEmpty()) {
            return null;
        }

        $current = Carbon::parse($startDate)->copy();
        $end = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $dateStr = $current->format('Y-m-d');
            $countOnDay = $this->countOnDay($overlappingLeaves, $dateStr);

            if ($countOnDay >= $maxSlots) {
                return sprintf(
                    'Phòng ban đã có %d người nghỉ phép vào ngày %s. Tối đa %d người/ngày (30%% của %d nhân viên đang làm việc).',
                    $countOnDay,
                    $current->format('d/m/Y'),
                    $maxSlots,
                    $headcount,
                );
            }

            $current->addDay();
        }

        return null;
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
            ->whereHas('employee', fn ($q) => $q->where('department_id', $departmentId))
            ->when($ignoreLeaveRequestId, fn ($q) => $q->where('id', '!=', $ignoreLeaveRequestId))
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->get();
    }

    /**
     * @param  Collection<int, LeaveRequest>  $overlappingLeaves
     */
    private function countOnDay(Collection $overlappingLeaves, string $dateStr): int
    {
        return $overlappingLeaves->filter(function (LeaveRequest $leave) use ($dateStr) {
            return $leave->start_date->format('Y-m-d') <= $dateStr
                && $leave->end_date->format('Y-m-d') >= $dateStr;
        })->count();
    }
}
