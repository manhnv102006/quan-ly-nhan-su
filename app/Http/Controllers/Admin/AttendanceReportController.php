<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $attendances = Attendance::with([
            'employee.department',
            'shift'
        ])
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get();

        $stats = [
            'present' => 0,
            'late' => 0,
            'leave' => 0,
            'absent' => 0,
            'total_hours' => 0,
            'late_minutes' => 0,
            'top_late_employee' => null,
        ];

        $lateEmployees = [];

        foreach ($attendances as $attendance) {

            if (isset($stats[$attendance->status])) {
                $stats[$attendance->status]++;
            }

            $stats['total_hours'] += (float) ($attendance->work_hours ?? 0);

            $attendance->late_minutes = 0;

            if (
                $attendance->check_in &&
                $attendance->shift &&
                $attendance->shift->start_time
            ) {

                $checkIn = Carbon::parse(
                    $attendance->check_in
                );

                $shiftStart = Carbon::parse(
                    $attendance->attendance_date
                )->setTimeFromTimeString(
                    $attendance->shift->start_time
                );

                if ($checkIn->gt($shiftStart)) {

                    $attendance->late_minutes =
                        $shiftStart->diffInMinutes($checkIn);

                    $stats['late_minutes'] +=
                        $attendance->late_minutes;

                    $employeeName =
                        $attendance->employee->full_name;

                    $lateEmployees[$employeeName] =
                        ($lateEmployees[$employeeName] ?? 0)
                        + $attendance->late_minutes;
                }
            }
        }

        if (!empty($lateEmployees)) {

            arsort($lateEmployees);

            $stats['top_late_employee'] =
                array_key_first($lateEmployees);
        }

        return view(
            'admin.attendance-reports.index',
            compact(
                'attendances',
                'stats',
                'month',
                'year'
            )
        );
    }
}