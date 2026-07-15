<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Services\AccountantAttendanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        protected AccountantAttendanceService $attendanceService,
    ) {}

    public function index(Request $request): View
    {
        if ($request->filled('employee_id')) {
            return $this->employeeTimesheet($request->integer('employee_id'), $request);
        }

        if ($request->filled('department_id')) {
            return $this->department(Department::findOrFail($request->department_id), $request);
        }

        $departments = Department::query()
            ->where('status', 'active')
            ->withCount('employees')
            ->orderBy('department_name')
            ->get()
            ->map(function (Department $department) {
                $employeeIds = $department->employees()->pluck('id');
                $department->today_present = $employeeIds->isEmpty()
                    ? 0
                    : Attendance::query()
                        ->whereIn('employee_id', $employeeIds)
                        ->whereDate('attendance_date', today())
                        ->whereIn('status', ['present', 'late'])
                        ->count();

                return $department;
            });

        return view('accountant.attendance.index', [
            'departments' => $departments,
            'today' => today()->format('d/m/Y'),
            'currentMonth' => now()->format('Y-m'),
        ]);
    }

    public function timesheet(Request $request): View
    {
        $month = $request->input('month', now()->format('Y-m'));
        [$year, $monthNum] = $this->attendanceService->parseMonth($month);

        $query = Employee::query()
            ->where('status', 'active')
            ->with(['department', 'position'])
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->department_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('full_name');

        $employees = $query->paginate(25)->withQueryString();
        $statsMap = $this->attendanceService->statsForEmployees($employees->pluck('id')->all(), $year, $monthNum);

        $employees->getCollection()->transform(function (Employee $employee) use ($statsMap) {
            $stats = $statsMap->get($employee->id);
            $employee->attendance_stats = $stats;
            $employee->payable_days = $this->attendanceService->payableDays($stats);

            return $employee;
        });

        $totals = [
            'payable_days' => $employees->getCollection()->sum('payable_days'),
            'total_hours' => round($statsMap->sum(fn ($s) => (float) $s->total_hours), 1),
            'overtime_hours' => round($statsMap->sum(fn ($s) => (float) $s->overtime_hours), 1),
        ];

        $departments = Department::where('status', 'active')->orderBy('department_name')->get();

        return view('accountant.attendance.timesheet', [
            'employees' => $employees,
            'departments' => $departments,
            'month' => $month,
            'year' => $year,
            'monthNum' => $monthNum,
            'totals' => $totals,
        ]);
    }

    public function show(Attendance $attendance): View
    {
        $attendance->load(['employee.department', 'employee.position', 'shift']);

        $attendance->setRelation(
            'employeeShift',
            \App\Models\EmployeeShift::with('shift')
                ->where('employee_id', $attendance->employee_id)
                ->whereDate('work_date', $attendance->attendance_date)
                ->first()
        );

        return view('accountant.attendance.show', compact('attendance'));
    }

    protected function department(Department $department, Request $request): View
    {
        $month = $request->input('month', now()->format('Y-m'));
        [$year, $monthNum] = $this->attendanceService->parseMonth($month);
        $search = trim((string) $request->input('search', ''));

        $employees = $department->employees()
            ->where('status', 'active')
            ->with(['position'])
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            }))
            ->orderBy('full_name')
            ->get();

        $statsMap = $this->attendanceService->statsForEmployees($employees->pluck('id')->all(), $year, $monthNum);

        $employees->transform(function (Employee $employee) use ($statsMap) {
            $stats = $statsMap->get($employee->id);
            $employee->attendance_stats = $stats;
            $employee->payable_days = $this->attendanceService->payableDays($stats);

            return $employee;
        });

        $totals = [
            'payable_days' => $employees->sum('payable_days'),
            'total_hours' => round($statsMap->sum(fn ($s) => (float) $s->total_hours), 1),
            'absent' => (int) $statsMap->sum(fn ($s) => (int) $s->absent),
            'leave' => (int) $statsMap->sum(fn ($s) => (int) $s->leave),
        ];

        return view('accountant.attendance.department', [
            'department' => $department,
            'employees' => $employees,
            'month' => $month,
            'search' => $search,
            'totals' => $totals,
        ]);
    }

    protected function employeeTimesheet(int $employeeId, Request $request): View
    {
        $employee = Employee::with(['department', 'position'])->findOrFail($employeeId);
        $month = $request->input('month', now()->format('Y-m'));
        [$year, $monthNum] = $this->attendanceService->parseMonth($month);

        $filters = [
            'month' => $monthNum,
            'year' => $year,
            'status' => $request->input('status', ''),
        ];

        $attendances = $this->attendanceService->attendancesForEmployee(
            $employee,
            $year,
            $monthNum,
            $filters['status'] ?: null,
        );

        $summary = $this->attendanceService->summaryFromCollection($attendances);

        return view('accountant.attendance.employee', [
            'employee' => $employee,
            'department' => $employee->department,
            'attendances' => $attendances,
            'summary' => $summary,
            'filters' => $filters,
            'month' => $month,
        ]);
    }
}
