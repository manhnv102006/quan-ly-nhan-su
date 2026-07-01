<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use App\Services\OvertimeSettlementService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class EmployeeAttendanceService
{
    public const GRACE_MINUTES = 5;

    public const AUTO_CHECKOUT_MINUTES = 5;

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
            $this->performFullDayCheckOut($attendance, $now);
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

            $attendance->check_out = $now;
        }

        $attendance->work_hours = $this->calculateWorkHours($attendance, $isFullDay);
        $attendance->save();

        $settled = $this->overtimeSettlement->settleAfterCheckout($employee, $attendance->fresh(), $isFullDay);

        return $attendance->fresh();
    }

    public function processAutoCheckouts(?Employee $employee = null, ?Carbon $now = null): int
    {
        $now ??= Carbon::now();
        $today = $now->copy()->startOfDay();
        $processed = 0;

        $query = Attendance::query()
            ->with(['shift', 'employee'])
            ->whereDate('attendance_date', $today);

        if ($employee) {
            $query->where('employee_id', $employee->id);
        }

        foreach ($query->get() as $attendance) {
            if (! $attendance->shift || ! $attendance->employee) {
                continue;
            }

            if ($this->applyAutoCheckouts($attendance, $now)) {
                $processed++;
            }
        }

        return $processed;
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

    private function performFullDayCheckOut(Attendance $attendance, Carbon $now): void
    {
        if ($attendance->morning_check_in && ! $attendance->morning_check_out) {
            $attendance->morning_check_out = $now;
        } elseif ($attendance->afternoon_check_in && ! $attendance->afternoon_check_out) {
            $attendance->afternoon_check_out = $now;
        } else {
            throw ValidationException::withMessages([
                'attendance' => 'Không xác định được buổi cần chấm công ra.',
            ]);
        }
    }

    private function assertCanCheckIn(Carbon $now, Carbon $sessionStart, Carbon $sessionEnd): void
    {
        if ($now->lt($sessionStart)) {
            throw ValidationException::withMessages([
                'attendance' => 'Chưa đến giờ check-in. Ca làm bắt đầu lúc '.$sessionStart->format('H:i').'.',
            ]);
        }

        if ($now->gt($sessionEnd)) {
            throw ValidationException::withMessages([
                'attendance' => 'Đã qua giờ kết ca ('.$sessionEnd->format('H:i').'). Không thể check-in.',
            ]);
        }
    }

    private function applyAutoCheckouts(Attendance $attendance, Carbon $now): bool
    {
        $isFullDay = $this->isFullDayShift($attendance->shift);
        $date = $attendance->attendance_date;
        $changed = false;

        if ($isFullDay) {
            $changed = $this->autoCheckoutSession(
                $attendance,
                Carbon::parse($date)->setTime(12, 0),
                'morning_check_in',
                'morning_check_out',
                $now,
            ) || $changed;

            $changed = $this->autoCheckoutSession(
                $attendance,
                Carbon::parse($date)->setTime(17, 0),
                'afternoon_check_in',
                'afternoon_check_out',
                $now,
            ) || $changed;
        } else {
            $sessionEnd = Carbon::parse($attendance->shift->end_time)->setDateFrom($date);
            $changed = $this->autoCheckoutSession(
                $attendance,
                $sessionEnd,
                'check_in',
                'check_out',
                $now,
            );
        }

        if ($changed) {
            $attendance->work_hours = $this->calculateWorkHours($attendance, $isFullDay);
            $attendance->save();

            if ($attendance->employee) {
                $this->overtimeSettlement->settleAfterCheckout(
                    $attendance->employee,
                    $attendance->fresh(),
                    $isFullDay
                );
            }
        }

        return $changed;
    }

    private function autoCheckoutSession(
        Attendance $attendance,
        Carbon $sessionEnd,
        string $checkInField,
        string $checkOutField,
        Carbon $now,
    ): bool {
        if (! $attendance->{$checkInField} || $attendance->{$checkOutField}) {
            return false;
        }

        $autoCheckoutAt = $sessionEnd->copy()->addMinutes(self::AUTO_CHECKOUT_MINUTES);

        if ($now->lt($autoCheckoutAt)) {
            return false;
        }

        $attendance->{$checkOutField} = $autoCheckoutAt;

        return true;
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
        $autoCheckoutAt = $sessionEnd->copy()->addMinutes(self::AUTO_CHECKOUT_MINUTES);

        $priorDone = $requiresPriorCheckout === null || filled($attendance->{$requiresPriorCheckout});
        $canCheckIn = false;
        $canCheckOut = false;
        $statusMessage = '';
        $statusTone = 'idle';

        if (! $checkIn) {
            if (! $priorDone) {
                $statusMessage = 'Hoàn thành buổi trước để check-in';
                $statusTone = 'waiting';
            } elseif ($now->lt($sessionStart)) {
                $statusMessage = 'Check-in mở lúc '.$sessionStart->format('H:i');
                $statusTone = 'upcoming';
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
            $canCheckOut = true;
            if ($now->gte($autoCheckoutAt)) {
                $statusMessage = 'Hệ thống sẽ tự check-out lúc '.$autoCheckoutAt->format('H:i');
                $statusTone = 'warning';
            } else {
                $statusMessage = 'Đã check-in — có thể check-out thủ công';
                $statusTone = 'active';
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
            'auto_checkout_at' => $autoCheckoutAt,
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
}
