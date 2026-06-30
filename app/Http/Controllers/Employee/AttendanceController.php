<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(): View
    {
        $employee   = Employee::where('user_id', Auth::id())->firstOrFail();
        $todayShift = $employee->todayShift();
        $today      = Carbon::today();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        $isFullDayShift = $todayShift && $this->isFullDayShift($todayShift->shift);

        // Kiểm tra vượt giờ ca -> gợi ý tạo đơn tăng ca
        $overtimeInfo = null;
        if ($attendance && $todayShift && $todayShift->shift) {
            $lastCheckOut = $isFullDayShift
                ? $attendance->afternoon_check_out
                : $attendance->check_out;

            if ($lastCheckOut) {
                $shiftEnd = Carbon::parse($todayShift->shift->end_time)->setDateFrom($today);

                if ($lastCheckOut->gt($shiftEnd->copy()->addMinutes(15))) {
                    $overtimeInfo = [
                        'date'       => $today->format('Y-m-d'),
                        'start_time' => $shiftEnd->format('H:i'),
                        'end_time'   => $lastCheckOut->format('H:i'),
                        'minutes'    => (int) round($shiftEnd->diffInMinutes($lastCheckOut)),
                    ];
                }
            }
        }

        return view('employee.attendance.index', compact(
            'employee',
            'todayShift',
            'attendance',
            'isFullDayShift',
            'overtimeInfo'
        ));
    }

    public function checkIn($shift): RedirectResponse
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        $now      = Carbon::now();
        $today    = Carbon::today();

        $todayShift = $employee->todayShift();
        if (! $todayShift || ! $todayShift->shift) {
            return back()->with('error', 'Bạn chưa được gán ca làm hôm nay.');
        }

        $shiftModel = $todayShift->shift;
        $isFullDay  = $this->isFullDayShift($shiftModel);

        $attendance             = Attendance::firstOrNew([
            'employee_id'     => $employee->id,
            'attendance_date' => $today,
        ]);
        $attendance->shift_id   = $shiftModel->id;

        if ($isFullDay) {
            $noon = Carbon::today()->setTime(12, 30);

            if ($now->lt($noon) && ! $attendance->morning_check_in) {
                $attendance->morning_check_in = $now;
                $sessionStart = Carbon::today()->setTime(8, 0);
            } elseif (! $attendance->afternoon_check_in) {
                $attendance->afternoon_check_in = $now;
                $sessionStart = Carbon::today()->setTime(13, 0);
            } else {
                return back()->with('error', 'Bạn đã chấm công đủ 2 buổi hôm nay.');
            }
        } else {
            if ($attendance->check_in) {
                return back()->with('error', 'Bạn đã chấm công vào hôm nay.');
            }
            $attendance->check_in = $now;
            $sessionStart         = Carbon::parse($shiftModel->start_time)->setDateFrom($today);
        }

        $lateMinutes              = max(0, (int) $sessionStart->diffInMinutes($now, false));
        $attendance->late_minutes = ($attendance->late_minutes ?? 0) + $lateMinutes;
        $attendance->status       = $lateMinutes > 0 ? 'late' : 'present';
        $attendance->save();

        return back()->with('success', 'Chấm công vào giờ thành công.');
    }


    private function isFullDayShift($shift): bool
    {
        return $shift && str_contains(mb_strtolower($shift->shift_name), 'hành chính');
    }

    private function calculateWorkHours(Attendance $attendance, bool $isFullDay): float
    {
        $hours = 0;

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
}