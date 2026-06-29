<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(): View
    {
        $employee = Employee::where(
            'user_id',
            Auth::id()
        )->firstOrFail();

        $todayShift = $employee->todayShift();

        $attendance = Attendance::where(
                'employee_id',
                $employee->id
            )
            ->whereDate(
                'attendance_date',
                Carbon::today()
            )
            ->first();

        return view(
            'employee.attendance.index',
            compact(
                'employee',
                'todayShift',
                'attendance'
            )
        );
    }
}