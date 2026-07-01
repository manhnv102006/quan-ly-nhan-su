<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OvertimeSettlementService
{
    /**
     * Quyết toán giờ tăng ca sau khi nhân viên check-out (nếu có đơn đã duyệt).
     */
    public function settleAfterCheckout(Employee $employee, Attendance $attendance, bool $isFullDayShift): ?OvertimeRequest
    {
        if (! $this->isFinalCheckout($attendance, $isFullDayShift)) {
            return null;
        }

        $checkOutAt = $this->resolveCheckOutTime($attendance, $isFullDayShift);
        if (! $checkOutAt) {
            return null;
        }

        return $this->settle($employee, $attendance, $checkOutAt);
    }

    /**
     * Quyết toán khi đơn được duyệt sau khi nhân viên đã check-out.
     */
    public function settleIfCheckedOut(OvertimeRequest $overtimeRequest): ?OvertimeRequest
    {
        if ($overtimeRequest->status !== OvertimeRequest::STATUS_APPROVED) {
            return null;
        }

        $overtimeRequest->loadMissing('employee');
        $employee = $overtimeRequest->employee;
        if (! $employee) {
            return null;
        }

        $attendance = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $overtimeRequest->work_date)
            ->with('shift')
            ->first();

        if (! $attendance) {
            return null;
        }

        $isFullDay = $attendance->shift && str_contains(mb_strtolower($attendance->shift->shift_name), 'hành chính');
        $checkOutAt = $this->resolveCheckOutTime($attendance, $isFullDay);

        if (! $checkOutAt) {
            return null;
        }

        return $this->settle($employee, $attendance, $checkOutAt, $overtimeRequest);
    }

    protected function settle(
        Employee $employee,
        Attendance $attendance,
        Carbon $checkOutAt,
        ?OvertimeRequest $overtimeRequest = null,
    ): ?OvertimeRequest {
        $workDate = $attendance->attendance_date ?? $checkOutAt->copy()->startOfDay();

        $overtimeRequest ??= OvertimeRequest::query()
            ->where('employee_id', $employee->id)
            ->whereDate('work_date', $workDate)
            ->where('status', OvertimeRequest::STATUS_APPROVED)
            ->first();

        if (! $overtimeRequest) {
            return null;
        }

        $actualHours = $this->calculateActualHours($overtimeRequest, $attendance, $checkOutAt);

        return DB::transaction(function () use ($overtimeRequest, $attendance, $actualHours) {
            if ($actualHours <= 0) {
                return $overtimeRequest;
            }

            $overtimeRequest->update([
                'total_hours' => $actualHours,
                'status' => OvertimeRequest::STATUS_COMPLETED,
            ]);

            $attendance->update([
                'is_overtime' => true,
                'overtime_hours' => $actualHours,
            ]);

            return $overtimeRequest->fresh();
        });
    }

    protected function calculateActualHours(
        OvertimeRequest $overtimeRequest,
        Attendance $attendance,
        Carbon $checkOutAt,
    ): float {
        $workDate = Carbon::parse($overtimeRequest->work_date)->startOfDay();

        $approvedStart = Carbon::parse($overtimeRequest->start_time)->setDateFrom($workDate);
        $approvedEnd = Carbon::parse($overtimeRequest->end_time)->setDateFrom($workDate);

        $shiftEnd = $attendance->shift
            ? Carbon::parse($attendance->shift->end_time)->setDateFrom($workDate)
            : $approvedStart;

        $actualStart = $approvedStart->greaterThan($shiftEnd) ? $approvedStart : $shiftEnd;
        $actualEnd = $checkOutAt->lessThan($approvedEnd) ? $checkOutAt : $approvedEnd;

        if ($actualEnd->lte($actualStart)) {
            return 0;
        }

        return round($actualStart->diffInMinutes($actualEnd) / 60, 2);
    }

    protected function isFinalCheckout(Attendance $attendance, bool $isFullDayShift): bool
    {
        if ($isFullDayShift) {
            return $attendance->afternoon_check_out !== null;
        }

        return $attendance->check_out !== null;
    }

    protected function resolveCheckOutTime(Attendance $attendance, bool $isFullDayShift): ?Carbon
    {
        if ($isFullDayShift) {
            return $attendance->afternoon_check_out;
        }

        return $attendance->check_out;
    }
}
