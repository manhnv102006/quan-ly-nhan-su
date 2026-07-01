<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Services\EmployeeAttendanceService;
use App\Support\DepartmentSummaryBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AttendanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);

        return view('admin.attendance-reports.index', [
            'month' => $month,
            'year' => $year,
            'departmentSummaries' => DepartmentSummaryBuilder::forAttendance($month, $year),
        ]);
    }

    public function department(Request $request, Department $department): View
    {
        [$month, $year, $allAttendances] = $this->loadReportData($request);

        $attendances = $this->filterByDepartment($allAttendances, $department->id);
        $stats = $this->buildStats($attendances);

        return view('admin.attendance-reports.department', [
            'attendances' => $attendances,
            'stats' => $stats,
            'month' => $month,
            'year' => $year,
            'selectedDepartment' => $department,
            'scopeLabel' => $department->department_name,
            'showDepartmentColumn' => false,
        ]);
    }

    /**
     * @return array{0: int, 1: int, 2: Collection<int, Attendance>}
     */
    private function loadReportData(Request $request): array
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);

        $allAttendances = Attendance::with(['employee.department', 'shift'])
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->orderBy('attendance_date')
            ->orderBy('employee_id')
            ->get();

        return [$month, $year, $allAttendances];
    }

    /**
     * @param  Collection<int, Attendance>  $attendances
     * @return Collection<int, Attendance>
     */
    private function filterByDepartment(Collection $attendances, int $departmentId): Collection
    {
        return $attendances->filter(
            fn (Attendance $attendance) => (int) ($attendance->employee?->department_id) === $departmentId
        )->values();
    }

    /**
     * @param  Collection<int, Attendance>  $attendances
     * @return array{
     *     present: int,
     *     late: int,
     *     leave: int,
     *     absent: int,
     *     total_hours: float,
     *     late_minutes: int,
     *     top_late_employee: ?string,
     *     employee_count: int,
     *     record_count: int,
     * }
     */
    private function buildStats(Collection $attendances): array
    {
        $stats = [
            'present' => 0,
            'late' => 0,
            'leave' => 0,
            'absent' => 0,
            'total_hours' => 0,
            'late_minutes' => 0,
            'top_late_employee' => null,
            'employee_count' => $attendances->pluck('employee_id')->unique()->count(),
            'record_count' => $attendances->count(),
        ];

        $lateEmployees = [];

        foreach ($attendances as $attendance) {
            if (isset($stats[$attendance->status])) {
                $stats[$attendance->status]++;
            }

            $stats['total_hours'] += (float) ($attendance->work_hours ?? 0);

            $lateMinutes = (int) ($attendance->late_minutes ?? 0);

            if ($lateMinutes <= 0 && $attendance->check_in && $attendance->shift?->start_time) {
                $checkIn = Carbon::parse($attendance->check_in);
                $shiftStart = Carbon::parse($attendance->attendance_date)
                    ->setTimeFromTimeString($attendance->shift->start_time);
                $graceDeadline = $shiftStart->copy()->addMinutes(EmployeeAttendanceService::GRACE_MINUTES);

                if ($checkIn->gt($graceDeadline)) {
                    $lateMinutes = (int) $graceDeadline->diffInMinutes($checkIn);
                }
            }

            $attendance->display_late_minutes = $lateMinutes;
            $stats['late_minutes'] += $lateMinutes;

            if ($lateMinutes > 0 && $attendance->employee) {
                $employeeName = $attendance->employee->full_name;
                $lateEmployees[$employeeName] = ($lateEmployees[$employeeName] ?? 0) + $lateMinutes;
            }
        }

        if ($lateEmployees !== []) {
            arsort($lateEmployees);
            $stats['top_late_employee'] = array_key_first($lateEmployees);
        }

        return $stats;
    }
}
