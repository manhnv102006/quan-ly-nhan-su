<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $employee = Employee::where('user_id', Auth::id())
            ->firstOrFail();

        $today = now()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        $shift = Shift::find($employee->shift_id);

        return view(
            'employee.attendance.index',
            compact(
                'employee',
                'attendance',
                'shift'
            )
        );
    }

    public function checkIn()
    {
        $employee = Employee::where('user_id', Auth::id())
            ->firstOrFail();

        $shift = Shift::findOrFail($employee->shift_id);

        $attendance = Attendance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'attendance_date' => now()->toDateString(),
            ],
            [
                'shift_id' => $shift->id,
                'status' => 'present',
            ]
        );

        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | Ca hành chính
        |--------------------------------------------------------------------------
        */

        if ($shift->isOfficeShift()) {

            if (!$attendance->morning_check_in) {

                $attendance->morning_check_in = $now;

                $late = Carbon::parse(
                    today()->format('Y-m-d') . ' 08:00:00'
                );

                if ($now->gt($late)) {

                    $attendance->late_minutes =
                        $late->diffInMinutes($now);

                    $attendance->status = 'late';
                }

                $attendance->save();

                return back()->with(
                    'success',
                    'Đã chấm công vào ca sáng.'
                );
            }

            if (!$attendance->afternoon_check_in) {

                $attendance->afternoon_check_in = $now;
                $attendance->save();

                return back()->with(
                    'success',
                    'Đã chấm công vào ca chiều.'
                );
            }

            return back()->with(
                'error',
                'Bạn đã chấm công đủ.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Các ca còn lại
        |--------------------------------------------------------------------------
        */

        if ($attendance->check_in) {

            return back()->with(
                'error',
                'Bạn đã chấm công.'
            );
        }

        $attendance->check_in = $now;

        $late = Carbon::parse(
            today()->format('Y-m-d') . ' ' . $shift->start_time
        );

        if ($now->gt($late)) {

            $attendance->late_minutes =
                $late->diffInMinutes($now);

            $attendance->status = 'late';
        }

        $attendance->save();

        return back()->with(
            'success',
            'Chấm công thành công.'
        );
    }
}