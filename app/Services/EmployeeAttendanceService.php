<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\OvertimeRequest;
use App\Models\Shift;
use App\Services\OvertimeSettlementService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class EmployeeAttendanceService
{
    public const GRACE_MINUTES = 5;

    public const EARLY_CHECK_IN_MINUTES = 60;

    public function __construct(
        private readonly OvertimeSettlementService $overtimeSettlement,
    ) {
    }

    /**
     * @return array{
     *     morning: array<string, mixed>,
     *     afternoon: array<string, mixed>,
     * }
     */
    public function fullDaySessions(Attendance $attendance, Carbon $date, ?Carbon $now = null): array
    {
        $now ??= Carbon::now();

        return [
            'morning' => $this->buildSessionState(
                $attendance,
                Carbon::parse($date)->setTime(8, 0),
                Carbon::parse($date)->setTime(12, 0),
                'morning_check_in',
                'morning_check_out',
                'morning_late_minutes',
                $now,
            ),
            'afternoon' => $this->buildSessionState(
                $attendance,
                Carbon::parse($date)->setTime(13, 0),
                Carbon::parse($date)->setTime(17, 0),
                'afternoon_check_in',
                'afternoon_check_out',
                'afternoon_late_minutes',
                $now,
                requiresPriorCheckout: 'morning_check_out',
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function regularSession(Attendance $attendance, Shift $shift, Carbon $date, ?Carbon $now = null): array
    {
        $now ??= Carbon::now();

        return $this->buildSessionState(
            $attendance,
            Carbon::parse($shift->start_time)->setDateFrom($date),
            Carbon::parse($shift->end_time)->setDateFrom($date),
            'check_in',
            'check_out',
            'late_minutes',
            $now,
        );
    }

    public function checkIn(Employee $employee, Shift $shift, bool $isFullDay, Carbon $now): Attendance
    {
        $today = $now->copy()->startOfDay();

        $this->assertWorkdayOrApprovedOvertime($employee, $now);

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'attendance_date' => $today,
        ]);
        $attendance->shift_id = $shift->id;

        if ($isFullDay) {
            $this->performFullDayCheckIn($attendance, $today, $now);
        } else {
            $this->performRegularCheckIn($attendance, $shift, $today, $now);
        }

        $attendance->status = $attendance->late_minutes > 0 ? 'late' : 'present';
        $attendance->save();

        return $attendance;
    }

    public function checkOut(Employee $employee, Attendance $attendance, bool $isFullDay, Carbon $now): Attendance
    {
        if ($isFullDay) {
            $this->performFullDayCheckOut($employee, $attendance, $now);
        } else {
            if (! $attendance->check_in) {
                throw ValidationException::withMessages([
                    'attendance' => 'Bạn chưa chấm công vào.',
                ]);
            }

            if ($attendance->check_out) {
                throw ValidationException::withMessages([
                    'attendance' => 'Bạn đã chấm công ra.',
                ]);
            }

            $sessionEnd = Carbon::parse($attendance->shift->end_time)->setDateFrom($attendance->attendance_date);
            $this->assertCheckOutAfterCheckIn($now, Carbon::parse($attendance->check_in));
            $this->assertCanCheckOut($employee, $attendance, $now, $sessionEnd);

            $attendance->check_out = $now;
        }

        $attendance->work_hours = $this->calculateWorkHours($attendance, $isFullDay);
        $attendance->save();

        $this->overtimeSettlement->settleAfterCheckout($employee, $attendance->fresh(), $isFullDay);

        return $attendance->fresh();
    }

    public function calculateLateMinutes(Carbon $checkIn, Carbon $sessionStart): int
    {
        $graceDeadline = $sessionStart->copy()->addMinutes(self::GRACE_MINUTES);

        if ($checkIn->lte($graceDeadline)) {
            return 0;
        }

        return (int) $graceDeadline->diffInMinutes($checkIn);
    }

    public function calculateWorkHours(Attendance $attendance, bool $isFullDay): float
    {
        $hours = 0.0;

        if ($isFullDay) {
            if ($attendance->morning_check_in && $attendance->morning_check_out) {
                $hours += $attendance->morning_check_in->diffInMinutes($attendance->morning_check_out) / 60;
            }
            if ($attendance->afternoon_check_in && $attendance->afternoon_check_out) {
                $hours += $attendance->afternoon_check_in->diffInMinutes($attendance->afternoon_check_out) / 60;
            }
        } elseif ($attendance->check_in && $attendance->check_out) {
            $hours += $attendance->check_in->diffInMinutes($attendance->check_out) / 60;
        }

        return round($hours, 2);
    }

    public function isFullDayShift(?Shift $shift): bool
    {
        return $shift && str_contains(mb_strtolower($shift->shift_name), 'hành chính');
    }

    private function performRegularCheckIn(Attendance $attendance, Shift $shift, Carbon $today, Carbon $now): void
    {
        if ($attendance->check_in) {
            throw ValidationException::withMessages([
                'attendance' => 'Bạn đã chấm công vào hôm nay.',
            ]);
        }

        $sessionStart = Carbon::parse($shift->start_time)->setDateFrom($today);
        $sessionEnd = Carbon::parse($shift->end_time)->setDateFrom($today);

        $this->assertCanCheckIn($now, $sessionStart, $sessionEnd);

        $attendance->check_in = $now;
        $attendance->late_minutes = $this->calculateLateMinutes($now, $sessionStart);
    }

    private function performFullDayCheckIn(Attendance $attendance, Carbon $today, Carbon $now): void
    {
        $morningStart = $today->copy()->setTime(8, 0);
        $morningEnd = $today->copy()->setTime(12, 0);
        $afternoonStart = $today->copy()->setTime(13, 0);
        $afternoonEnd = $today->copy()->setTime(17, 0);

        if ($now->lt($morningEnd) && ! $attendance->morning_check_in) {
            $this->assertCanCheckIn($now, $morningStart, $morningEnd);
            $attendance->morning_check_in = $now;
            $attendance->morning_late_minutes = $this->calculateLateMinutes($now, $morningStart);
        } elseif (! $attendance->afternoon_check_in) {
            if (! $attendance->morning_check_out) {
                throw ValidationException::withMessages([
                    'attendance' => 'Bạn cần hoàn thành check-out buổi sáng trước khi check-in buổi chiều.',
                ]);
            }

            $this->assertCanCheckIn($now, $afternoonStart, $afternoonEnd);
            $attendance->afternoon_check_in = $now;
            $attendance->afternoon_late_minutes = $this->calculateLateMinutes($now, $afternoonStart);
        } else {
            throw ValidationException::withMessages([
                'attendance' => 'Bạn đã chấm công đủ 2 buổi hôm nay.',
            ]);
        }

        $attendance->late_minutes = (int) $attendance->morning_late_minutes + (int) $attendance->afternoon_late_minutes;
    }

    private function performFullDayCheckOut(Employee $employee, Attendance $attendance, Carbon $now): void
    {
        $date = $attendance->attendance_date;

        if ($attendance->morning_check_in && ! $attendance->morning_check_out) {
            $sessionEnd = Carbon::parse($date)->setTime(12, 0);
            $this->assertCheckOutAfterCheckIn($now, Carbon::parse($attendance->morning_check_in));
            $this->assertCanCheckOut($employee, $attendance, $now, $sessionEnd);
            $attendance->morning_check_out = $now;
        } elseif ($attendance->afternoon_check_in && ! $attendance->afternoon_check_out) {
            $sessionEnd = Carbon::parse($date)->setTime(17, 0);
            $this->assertCheckOutAfterCheckIn($now, Carbon::parse($attendance->afternoon_check_in));
            $this->assertCanCheckOut($employee, $attendance, $now, $sessionEnd);
            $attendance->afternoon_check_out = $now;
        } else {
            throw ValidationException::withMessages([
                'attendance' => 'Không xác định được buổi cần chấm công ra.',
            ]);
        }
    }

    private function assertCanCheckIn(Carbon $now, Carbon $sessionStart, Carbon $sessionEnd): void
    {
        $earliestCheckIn = $this->earliestCheckInAt($sessionStart);

        if ($now->lt($earliestCheckIn)) {
            throw ValidationException::withMessages([
                'attendance' => 'Chưa đến giờ check-in. Có thể check-in từ lúc '.$earliestCheckIn->format('H:i').' (trước ca '.self::EARLY_CHECK_IN_MINUTES.' phút). Ca bắt đầu lúc '.$sessionStart->format('H:i').'.',
            ]);
        }

        if ($now->gt($sessionEnd)) {
            throw ValidationException::withMessages([
                'attendance' => 'Đã qua giờ kết ca ('.$sessionEnd->format('H:i').'). Không thể check-in.',
            ]);
        }
    }

    private function assertCanCheckOut(Employee $employee, Attendance $attendance, Carbon $now, Carbon $sessionEnd): void
    {
        if ($now->lt($sessionEnd)) {
            $earlyLeave = \App\Models\EarlyLeaveRequest::where('employee_id', $employee->id)
                ->whereDate('request_date', $attendance->attendance_date)
                ->where('status', \App\Models\EarlyLeaveRequest::STATUS_APPROVED)
                ->first();

            if ($earlyLeave) {
                // Được duyệt về sớm -> Không bị phạt
                return;
            }

            // Về sớm không phép -> Phạt 0.5 công
            $attendance->work_ratio = 0.5;
        }
    }

    private function assertCheckOutAfterCheckIn(Carbon $checkOutTime, Carbon $checkInTime): void
    {
        if ($checkOutTime->lte($checkInTime)) {
            throw ValidationException::withMessages([
                'attendance' => 'Giờ check-out phải sau giờ check-in ('.$checkInTime->format('H:i:s').').',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSessionState(
        Attendance $attendance,
        Carbon $sessionStart,
        Carbon $sessionEnd,
        string $checkInField,
        string $checkOutField,
        string $lateField,
        Carbon $now,
        ?string $requiresPriorCheckout = null,
    ): array {
        $checkIn = $attendance->{$checkInField};
        $checkOut = $attendance->{$checkOutField};
        $graceDeadline = $sessionStart->copy()->addMinutes(self::GRACE_MINUTES);
        $earliestCheckIn = $this->earliestCheckInAt($sessionStart);

        $priorDone = $requiresPriorCheckout === null || filled($attendance->{$requiresPriorCheckout});
        $canCheckIn = false;
        $canCheckOut = false;
        $statusMessage = '';
        $statusTone = 'idle';

        if (! $checkIn) {
            if (! $priorDone) {
                $statusMessage = 'Hoàn thành buổi trước để check-in';
                $statusTone = 'waiting';
            } elseif ($now->lt($earliestCheckIn)) {
                $statusMessage = 'Check-in mở lúc '.$earliestCheckIn->format('H:i').' (trước ca '.self::EARLY_CHECK_IN_MINUTES.' phút)';
                $statusTone = 'upcoming';
            } elseif ($now->lt($sessionStart)) {
                $canCheckIn = true;
                $statusMessage = 'Có thể check-in sớm — ca bắt đầu lúc '.$sessionStart->format('H:i');
                $statusTone = 'ready';
            } elseif ($now->gt($sessionEnd)) {
                $statusMessage = 'Đã qua giờ ca làm';
                $statusTone = 'missed';
            } else {
                $canCheckIn = true;
                if ($now->lte($graceDeadline)) {
                    $statusMessage = 'Trong thời gian cho phép (miễn trừ '.$this->graceLabel().')';
                    $statusTone = 'ready';
                } else {
                    $pendingLate = (int) $graceDeadline->diffInMinutes($now);
                    $statusMessage = 'Đang trễ '.$pendingLate.' phút — check-in ngay';
                    $statusTone = 'late';
                }
            }
        } elseif (! $checkOut) {
            $checkInAt = Carbon::parse($checkIn);
            if ($now->lte($checkInAt)) {
                $statusMessage = 'Check-out phải sau giờ check-in ('.$checkInAt->format('H:i:s').')';
                $statusTone = 'waiting';
            } elseif ($now->lt($sessionEnd)) {
                $statusMessage = 'Đang làm việc — check-out lúc '.$sessionEnd->format('H:i');
                $statusTone = 'active';
            } else {
                $canCheckOut = true;
                $statusMessage = 'Đã hết giờ ca — vui lòng check-out';
                $statusTone = 'warning';
            }
        } else {
            $statusMessage = 'Đã hoàn thành buổi này';
            $statusTone = 'completed';
        }

        return [
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'late_minutes' => (int) ($attendance->{$lateField} ?? 0),
            'session_start' => $sessionStart,
            'session_end' => $sessionEnd,
            'grace_deadline' => $graceDeadline,
            'can_check_in' => $canCheckIn,
            'can_check_out' => $canCheckOut,
            'status_message' => $statusMessage,
            'status_tone' => $statusTone,
            'pending_late_minutes' => (! $checkIn && $now->gt($graceDeadline) && $now->lte($sessionEnd))
                ? (int) $graceDeadline->diffInMinutes($now)
                : 0,
        ];
    }

    private function graceLabel(): string
    {
        return self::GRACE_MINUTES.' phút';
    }

    private function earliestCheckInAt(Carbon $sessionStart): Carbon
    {
        return $sessionStart->copy()->subMinutes(self::EARLY_CHECK_IN_MINUTES);
    }

    /**
     * Ngày nghỉ (Chủ Nhật / ngày lễ) chỉ được chấm công khi có đơn tăng ca đã duyệt.
     */
    private function assertWorkdayOrApprovedOvertime(Employee $employee, Carbon $now): void
    {
        $dayOff = $this->dayOffReason($now);

        if ($dayOff === null) {
            return;
        }

        if ($this->hasApprovedOvertimeOn($employee, $now)) {
            return;
        }

        throw ValidationException::withMessages([
            'attendance' => 'Hôm nay là '.$dayOff.', bạn không có lịch tăng ca được duyệt. Không thể chấm công.',
        ]);
    }

    /**
     * Nếu ngày là ngày nghỉ, trả về nhãn lý do ('Chủ Nhật' | 'ngày nghỉ lễ'); ngược lại null.
     */
    public function dayOffReason(Carbon $date): ?string
    {
        if ($date->isSunday()) {
            return 'Chủ Nhật';
        }

        $isHoliday = Holiday::inRange($date->format('Y-m-d'), $date->format('Y-m-d'))->exists();

        return $isHoliday ? 'ngày nghỉ lễ' : null;
    }

    public function hasApprovedOvertimeOn(Employee $employee, Carbon $date): bool
    {
        return OvertimeRequest::query()
            ->where('employee_id', $employee->id)
            ->whereDate('work_date', $date->format('Y-m-d'))
            ->whereIn('status', [
                OvertimeRequest::STATUS_APPROVED,
                OvertimeRequest::STATUS_COMPLETED,
            ])
            ->exists();
    }
}
