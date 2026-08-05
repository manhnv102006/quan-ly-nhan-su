<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Models\Shift;
use App\Services\EmployeeAttendanceService;
use App\Services\FaceAttendanceService;
use App\Services\FaceVerificationService;
use App\Services\OvertimeAttendanceService;
use App\Services\OvertimeSettlementService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly OvertimeSettlementService $overtimeSettlement,
        private readonly OvertimeAttendanceService $overtimeAttendance,
        private readonly EmployeeAttendanceService $attendanceService,
        private readonly FaceVerificationService $faceVerification,
        private readonly FaceAttendanceService $faceAttendance,
    ) {
    }

    public function index(): View
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        $todayShifts = $employee->todayShifts();
        $todayShift = $employee->todayShift();
        $today = Carbon::today();
        $now = Carbon::now();

        $todayAttendances = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->get();

        $isFullDayShift = $todayShift && $this->attendanceService->isFullDayShift($todayShift->shift);

        $attendance = $todayShift?->shift
            ? $todayAttendances->first(fn (Attendance $row) => (int) $row->shift_id === (int) $todayShift->shift->id)
            : null;
        $attendance ??= $todayAttendances->first();

        $shiftSessions = collect();

        foreach ($todayShifts as $assignedShift) {
            if (! $assignedShift->shift) {
                continue;
            }

            $shift = $assignedShift->shift;
            $shiftAttendance = $this->attendanceService->attendanceForShift(
                $todayAttendances,
                $shift,
                $employee->id,
                $today,
            );
            $isShiftFullDay = $this->attendanceService->isFullDayShift($shift);

            if ($isShiftFullDay) {
                $shiftSessions->push([
                    'shift' => $shift,
                    'isFullDay' => true,
                    'sessions' => $this->attendanceService->fullDaySessions($shiftAttendance, $today, $now),
                ]);
            } else {
                $shiftSessions->push([
                    'shift' => $shift,
                    'isFullDay' => false,
                    'session' => $this->attendanceService->regularSession(
                        $shiftAttendance,
                        $shift,
                        $today,
                        $now,
                    ),
                ]);
            }
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

        $dayOffReason = $this->attendanceService->dayOffReason($now);
        $hasApprovedOvertimeToday = $this->attendanceService->hasApprovedOvertimeOn($employee, $now);
        $isBlockedDayOff = $dayOffReason !== null && ! $hasApprovedOvertimeToday;

        $faceEnrolled = $employee->hasFaceEnrolled();
        $canFaceScan = $faceEnrolled
            && ! $isBlockedDayOff
            && $this->faceAttendance->canScanNow($employee, $now);

        return view('employee.attendance.index', compact(
            'employee',
            'todayShift',
            'todayShifts',
            'attendance',
            'isFullDayShift',
            'shiftSessions',
            'overtimeInfo',
            'overtimeSessions',
            'faceEnrolled',
            'canFaceScan',
            'dayOffReason',
            'hasApprovedOvertimeToday',
            'isBlockedDayOff',
        ));
    }

    /**
     * Quét khuôn mặt tự động chấm công (gọi từ webcam trên trang web).
     */
    public function faceScan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'image_base64' => ['required', 'string'],
        ]);

        $employee = Employee::where('user_id', Auth::id())->firstOrFail();

        if (! $employee->hasFaceEnrolled()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa đăng ký khuôn mặt. Liên hệ quản trị viên.',
            ], 422);
        }

        $verification = $this->faceVerification->verify($employee->id, $data['image_base64']);

        if (! $verification['verified']) {
            return response()->json([
                'success' => false,
                'message' => $verification['message'],
                'score' => $verification['score'],
            ], 422);
        }

        $result = $this->faceAttendance->recordAuto($employee, $verification['score']);

        $status = $result['success'] ? 200 : 422;

        return response()->json([
            'success' => $result['success'],
            'action' => $result['action'],
            'message' => $result['message'],
            'score' => $result['confidence'],
        ], $status);
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

        $assignedShift = $this->resolveTodayShift($employee, $shift);
        if (! $assignedShift) {
            return back()->with('error', 'Bạn chưa được gán ca làm này hôm nay.');
        }

        $isFullDay = $this->attendanceService->isFullDayShift($assignedShift);

        try {
            $this->attendanceService->checkIn($employee, $assignedShift, $isFullDay, $now);
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

        $assignedShift = $this->resolveTodayShift($employee, $shift);
        if (! $assignedShift) {
            return back()->with('error', 'Bạn chưa được gán ca làm này hôm nay.');
        }

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->where('shift_id', $assignedShift->id)
            ->first();

        if (! $attendance) {
            return back()->with('error', 'Bạn chưa chấm công vào ca này hôm nay.');
        }

        $isFullDay = $this->attendanceService->isFullDayShift($assignedShift);

        try {
            $this->attendanceService->checkOut($employee, $attendance, $isFullDay, $now);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', $e->validator->errors()->first());
        }

        return back()->with('success', 'Chấm công ra giờ thành công.');
    }

    private function resolveTodayShift(Employee $employee, $shiftId): ?Shift
    {
        foreach ($employee->todayShifts() as $employeeShift) {
            if ($employeeShift->shift && (int) $employeeShift->shift->id === (int) $shiftId) {
                return $employeeShift->shift;
            }
        }

        return null;
    }
}
