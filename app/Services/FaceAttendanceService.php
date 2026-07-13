<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class FaceAttendanceService
{
    public function __construct(
        private readonly EmployeeAttendanceService $attendanceService,
        private readonly NotificationService $notificationService,
    ) {
    }

    /**
     * Ghi nhận chấm công tự động (check-in hoặc check-out) sau khi xác thực khuôn mặt.
     *
     * @return array{success: bool, action: ?string, message: string, confidence: float}
     */
    public function recordAuto(Employee $employee, float $confidence): array
    {
        $now = Carbon::now();
        $today = Carbon::today();

        $todayShift = $employee->todayShift();
        if (! $todayShift || ! $todayShift->shift) {
            return [
                'success' => false,
                'action' => null,
                'message' => 'Bạn chưa được gán ca làm hôm nay.',
                'confidence' => $confidence,
            ];
        }

        $shift = $todayShift->shift;
        $isFullDay = $this->attendanceService->isFullDayShift($shift);

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        $action = $this->decideAction($attendance, $isFullDay);

        if ($action === 'done') {
            return [
                'success' => false,
                'action' => 'done',
                'message' => 'Bạn đã hoàn tất chấm công hôm nay.',
                'confidence' => $confidence,
            ];
        }

        try {
            if ($action === 'check-in') {
                $record = $this->attendanceService->checkIn($employee, $shift, $isFullDay, $now);
                $record->check_in_method = 'face';
                $record->recognition_confidence = $confidence;
                $record->save();
                $message = 'Chấm công vào bằng khuôn mặt thành công.';
            } else {
                if (! $attendance) {
                    return [
                        'success' => false,
                        'action' => null,
                        'message' => 'Bạn chưa chấm công vào hôm nay.',
                        'confidence' => $confidence,
                    ];
                }

                $record = $this->attendanceService->checkOut($employee, $attendance, $isFullDay, $now);
                $record->check_out_method = 'face';
                $record->recognition_confidence = $confidence;
                $record->save();
                $message = 'Chấm công ra bằng khuôn mặt thành công.';
            }
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'action' => $action,
                'message' => $e->validator->errors()->first(),
                'confidence' => $confidence,
            ];
        }

        $this->notifyEmployee($employee, $action, $now);

        return [
            'success' => true,
            'action' => $action,
            'message' => $message,
            'confidence' => $confidence,
        ];
    }

    /**
     * Kiểm tra nhân viên có thể quét mặt chấm công ngay bây giờ không.
     */
    public function canScanNow(Employee $employee, ?Attendance $attendance, ?Shift $shift, bool $isFullDay, Carbon $now): bool
    {
        if (! $shift) {
            return false;
        }

        $stub = $attendance ?? new Attendance([
            'employee_id' => $employee->id,
            'attendance_date' => $now->copy()->startOfDay(),
            'shift_id' => $shift->id,
        ]);

        if ($isFullDay) {
            $sessions = $this->attendanceService->fullDaySessions($stub, $now->copy()->startOfDay(), $now);

            return ($sessions['morning']['can_check_in'] ?? false)
                || ($sessions['morning']['can_check_out'] ?? false)
                || ($sessions['afternoon']['can_check_in'] ?? false)
                || ($sessions['afternoon']['can_check_out'] ?? false);
        }

        $session = $this->attendanceService->regularSession($stub, $shift, $now->copy()->startOfDay(), $now);

        return ($session['can_check_in'] ?? false) || ($session['can_check_out'] ?? false);
    }

    private function decideAction(?Attendance $attendance, bool $isFullDay): string
    {
        if (! $attendance) {
            return 'check-in';
        }

        if ($isFullDay) {
            if (! $attendance->morning_check_in) {
                return 'check-in';
            }
            if (! $attendance->morning_check_out) {
                return 'check-out';
            }
            if (! $attendance->afternoon_check_in) {
                return 'check-in';
            }
            if (! $attendance->afternoon_check_out) {
                return 'check-out';
            }

            return 'done';
        }

        if (! $attendance->check_in) {
            return 'check-in';
        }
        if (! $attendance->check_out) {
            return 'check-out';
        }

        return 'done';
    }

    private function notifyEmployee(Employee $employee, string $action, Carbon $now): void
    {
        if (! $employee->user_id) {
            return;
        }

        $label = $action === 'check-in' ? 'vào' : 'ra';
        $title = 'Chấm công bằng khuôn mặt';
        $content = "Bạn đã chấm công {$label} lúc {$now->format('H:i')} ngày {$now->format('d/m/Y')} bằng nhận diện khuôn mặt.";

        $this->notificationService->sendToUser($employee->user_id, $title, $content);
    }
}
