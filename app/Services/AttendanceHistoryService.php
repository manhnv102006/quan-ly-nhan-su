<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceHistoryService
{
    public function __construct(
        private readonly EmployeeAttendanceService $attendanceService,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function sessionRows(Collection $attendances, ?Carbon $asOf = null): Collection
    {
        $asOf ??= Carbon::now();
        $rows = collect();

        foreach ($attendances as $attendance) {
            $shiftName = $attendance->employeeShift?->shift?->shift_name
                ?? $attendance->shift?->shift_name
                ?? '—';
            $date = Carbon::parse($attendance->attendance_date);

            if ($this->attendanceService->isFullDayAttendance($attendance)) {
                $rows->push($this->buildSessionRow(
                    $attendance,
                    $date,
                    $shiftName,
                    'Buổi sáng',
                    $attendance->morning_check_in,
                    $attendance->morning_check_out,
                    (int) ($attendance->morning_late_minutes ?? 0),
                    (int) ($attendance->morning_early_minutes ?? 0),
                    $date->copy()->setTime(12, 0),
                    $asOf,
                ));
                $rows->push($this->buildSessionRow(
                    $attendance,
                    $date,
                    $shiftName,
                    'Buổi chiều',
                    $attendance->afternoon_check_in,
                    $attendance->afternoon_check_out,
                    (int) ($attendance->afternoon_late_minutes ?? 0),
                    (int) ($attendance->afternoon_early_minutes ?? 0),
                    $date->copy()->setTime(17, 0),
                    $asOf,
                ));
            } elseif (in_array($attendance->status, ['absent', 'leave'], true)
                && ! $attendance->check_in
                && ! $attendance->morning_check_in) {
                $rows->push([
                    'attendance' => $attendance,
                    'attendance_id' => $attendance->id,
                    'date' => $date,
                    'session_label' => 'Cả ngày',
                    'shift_name' => $shiftName,
                    'check_in' => null,
                    'check_out' => null,
                    'check_in_method' => null,
                    'check_out_method' => null,
                    'late_minutes' => 0,
                    'early_minutes' => 0,
                    'missing_checkout' => false,
                    'status' => $attendance->status,
                    'work_hours' => (float) ($attendance->work_hours ?? 0),
                ]);
            } else {
                $shift = $attendance->relationLoaded('shift') ? $attendance->shift : null;
                $sessionEnd = $shift
                    ? Carbon::parse($shift->end_time)->setDateFrom($date)
                    : $date->copy()->setTime(17, 0);

                $rows->push($this->buildSessionRow(
                    $attendance,
                    $date,
                    $shiftName,
                    'Ca làm',
                    $attendance->check_in,
                    $attendance->check_out,
                    (int) ($attendance->late_minutes ?? 0),
                    (int) ($attendance->early_minutes ?? 0),
                    $sessionEnd,
                    $asOf,
                ));
            }
        }

        return $rows;
    }

    /**
     * @return array<string, int|float>
     */
    public function summaryFromRows(Collection $sessionRows, Collection $attendances): array
    {
        $checkIns = $sessionRows->filter(fn (array $row) => filled($row['check_in']))->count();
        $checkOuts = $sessionRows->filter(fn (array $row) => filled($row['check_out']))->count();
        $missingCheckouts = $sessionRows->where('missing_checkout', true)->count();

        return [
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'leave' => $attendances->where('status', 'leave')->count(),
            'payable_days' => $attendances->whereIn('status', ['present', 'late'])->count(),
            'total_hours' => round((float) $attendances->sum('work_hours'), 1),
            'overtime_hours' => round((float) $attendances->sum('overtime_hours'), 1),
            'late_minutes' => (int) $attendances->sum('late_minutes'),
            'check_ins' => $checkIns,
            'check_outs' => $checkOuts,
            'missing_checkouts' => $missingCheckouts,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSessionRow(
        Attendance $attendance,
        Carbon $date,
        string $shiftName,
        string $sessionLabel,
        mixed $checkIn,
        mixed $checkOut,
        int $lateMinutes,
        int $earlyMinutes,
        Carbon $sessionEnd,
        Carbon $asOf,
    ): array {
        $checkInAt = $checkIn ? Carbon::parse($checkIn) : null;
        $checkOutAt = $checkOut ? Carbon::parse($checkOut) : null;
        $missingCheckout = $checkInAt !== null
            && $checkOutAt === null
            && $asOf->gte($sessionEnd);

        return [
            'attendance' => $attendance,
            'attendance_id' => $attendance->id,
            'date' => $date,
            'session_label' => $sessionLabel,
            'shift_name' => $shiftName,
            'check_in' => $checkInAt,
            'check_out' => $checkOutAt,
            'check_in_method' => $checkInAt ? ($attendance->check_in_method ?? 'manual') : null,
            'check_out_method' => $checkOutAt ? ($attendance->check_out_method ?? 'manual') : null,
            'late_minutes' => $lateMinutes,
            'early_minutes' => $earlyMinutes,
            'missing_checkout' => $missingCheckout,
            'status' => $attendance->status,
            'work_hours' => (float) ($attendance->work_hours ?? 0),
        ];
    }
}
