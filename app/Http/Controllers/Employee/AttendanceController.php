<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Services\EmployeeAttendanceService;
use App\Services\OvertimeAttendanceService;
use App\Services\OvertimeSettlementService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly OvertimeSettlementService $overtimeSettlement,
        private readonly OvertimeAttendanceService $overtimeAttendance,
        private readonly EmployeeAttendanceService $attendanceService,
    ) {
    }

    public function index(): View
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        $todayShift = $employee->todayShift();
        $today = Carbon::today();
        $now = Carbon::now();

        $this->attendanceService->processAutoCheckouts($employee, $now);

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        $isFullDayShift = $todayShift && $this->attendanceService->isFullDayShift($todayShift->shift);

        $attendanceSessions = null;
        $regularSession = null;

        $attendanceStub = $attendance ?? new Attendance([
            'employee_id' => $employee->id,
            'attendance_date' => $today,
            'shift_id' => $todayShift?->shift?->id,
        ]);

        if ($isFullDayShift && $todayShift?->shift) {
            $attendanceSessions = $this->attendanceService->fullDaySessions($attendanceStub, $today, $now);
        } elseif ($todayShift?->shift) {
            $regularSession = $this->attendanceService->regularSession(
                $attendanceStub,
                $todayShift->shift,
                $today,
                $now,
            );
        }

        $overtimeInfo = null;
        if ($attendance && $todayShift && $todayShift->shift) {
            $lastCheckOut = $isFullDayShift
                ? $attendance->afternoon_check_out
                : $attendance->check_out;

            if ($lastCheckOut) {
                $shiftEnd = Carbon::parse($todayShift->shift->end_time)->setDateFrom($today);

                if ($lastCheckOut->gt($shiftEnd->copy()->addMinutes(15))) {
                    $hasOpenRequest = OvertimeRequest::query()
                        ->where('employee_id', $employee->id)
                        ->whereDate('work_date', $today)
                        ->whereIn('status', [
                            OvertimeRequest::STATUS_PENDING,
                            OvertimeRequest::STATUS_APPROVED,
                        ])
                        ->exists();

                    if (! $hasOpenRequest) {
                        $overtimeInfo = [
                            'date' => $today->format('Y-m-d'),
                            'start_time' => $shiftEnd->format('H:i'),
                            'end_time' => $lastCheckOut->format('H:i'),
                            'minutes' => (int) round($shiftEnd->diffInMinutes($lastCheckOut)),
                        ];
                    }
                }
            }
        }

        $overtimeSessions = $this->overtimeAttendance->sessionsForDate($employee, $today);

        return view('employee.attendance.index', compact(
            'employee',
            'todayShift',
            'attendance',
            'isFullDayShift',
            'attendanceSessions',
            'regularSession',
            'overtimeInfo',
            'overtimeSessions',
        ));
    }

    public function overtimeCheckIn(OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();

        try {
            $this->overtimeAttendance->checkIn($overtimeRequest, $employee);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', $e->validator->errors()->first());
        }

        return back()->with('success', 'Check-in tăng ca thành công.');
    }

    public function overtimeCheckOut(OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();

        try {
            $completed = $this->overtimeAttendance->checkOut($overtimeRequest, $employee);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', $e->validator->errors()->first());
        }

        return back()->with(
            'success',
            'Check-out tăng ca thành công. Đã ghi nhận '.$completed->total_hours.' giờ.'
        );
    }

    public function checkIn($shift): RedirectResponse
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        $now = Carbon::now();

        $todayShift = $employee->todayShift();
        if (! $todayShift || ! $todayShift->shift) {
            return back()->with('error', 'Bạn chưa được gán ca làm hôm nay.');
        }

        $isFullDay = $this->attendanceService->isFullDayShift($todayShift->shift);

        try {
            $this->attendanceService->checkIn($employee, $todayShift->shift, $isFullDay, $now);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', $e->validator->errors()->first());
        }

        return back()->with('success', 'Chấm công vào giờ thành công.');
    }

    public function checkOut($shift): RedirectResponse
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        $now = Carbon::now();
        $today = Carbon::today();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (! $attendance) {
            return back()->with('error', 'Bạn chưa chấm công vào hôm nay.');
        }

        $isFullDay = $this->attendanceService->isFullDayShift($attendance->shift);

        try {
            $this->attendanceService->checkOut($employee, $attendance, $isFullDay, $now);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', $e->validator->errors()->first());
        }

        return back()->with('success', 'Chấm công ra giờ thành công.');
    }
}
