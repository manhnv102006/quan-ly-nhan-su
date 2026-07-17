<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AccountantAttendanceService;
use App\Services\LeaderScopeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly LeaderScopeService $scope,
        private readonly AccountantAttendanceService $attendanceService,
    ) {
    }

    public function index(Request $request): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $month = $request->input('month', now()->format('Y-m'));
        [$year, $monthNum] = $this->attendanceService->parseMonth($month);
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $employees = $this->scope->teamMembersQuery($leader)
            ->with(['department', 'position'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        $statsMap = $this->attendanceService->statsForEmployees(
            $employees->pluck('id')->map(fn ($id) => (int) $id)->all(),
            $year,
            $monthNum,
        );

        $employees->getCollection()->transform(function (Employee $employee) use ($statsMap) {
            $stats = $statsMap->get($employee->id);
            $employee->attendance_stats = $stats;
            $employee->payable_days = $this->attendanceService->payableDays($stats);

            return $employee;
        });

        $teamIds = $this->scope->teamMemberIds($leader);

        $monthlySummary = $teamIds === [] ? [
            'payable_days' => 0,
            'total_hours' => 0,
            'late_days' => 0,
        ] : [
            'payable_days' => (int) Attendance::query()
                ->whereIn('employee_id', $teamIds)
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $monthNum)
                ->whereIn('status', ['present', 'late'])
                ->count(),
            'total_hours' => round((float) Attendance::query()
                ->whereIn('employee_id', $teamIds)
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $monthNum)
                ->sum('work_hours'), 1),
            'late_days' => (int) Attendance::query()
                ->whereIn('employee_id', $teamIds)
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $monthNum)
                ->where('status', 'late')
                ->count(),
        ];

        $detailEmployee = null;
        $detailAttendances = collect();

        if ($request->filled('employee_id')) {
            $detailEmployee = Employee::query()->find($request->integer('employee_id'));
            if ($detailEmployee && $this->scope->managesEmployee($leader, $detailEmployee)) {
                $detailAttendances = $this->attendanceService
                    ->attendancesForEmployee($detailEmployee, $year, $monthNum, $status)
                    ->load('shift');
            } else {
                $detailEmployee = null;
            }
        }

        return view('leader.attendance.index', compact(
            'leader',
            'employees',
            'month',
            'year',
            'monthNum',
            'search',
            'status',
            'monthlySummary',
            'detailEmployee',
            'detailAttendances',
        ));
    }
}
