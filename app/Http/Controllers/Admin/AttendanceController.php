<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Support\DepartmentSummaryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(): View
    {
        return view('admin.attendances.index', [
            'departmentSummaries' => DepartmentSummaryBuilder::forAttendanceManagement(),
        ]);
    }

    public function department(Request $request, Department $department): View
    {
        $search = trim((string) $request->input('search', ''));

        $employeesQuery = Employee::query()
            ->where('department_id', $department->id)
            ->where('status', 'active')
            ->with('position')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            }))
            ->orderBy('full_name');

        $employees = $employeesQuery->paginate(15)->withQueryString();

        // Tổng hợp thống kê chấm công theo nhân viên trong tháng hiện tại
        $month      = now()->month;
        $year       = now()->year;
        $empIds     = $employees->pluck('id')->all();

        $statsMap = DB::table('attendances')
            ->selectRaw('employee_id,
                COUNT(*) as total,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = "late"    THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = "absent"  THEN 1 ELSE 0 END) as absent,
                SUM(work_hours) as total_hours')
            ->whereIn('employee_id', $empIds)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        // Ngày cuối cùng chấm công
        $lastDateMap = DB::table('attendances')
            ->selectRaw('employee_id, MAX(attendance_date) as last_date')
            ->whereIn('employee_id', $empIds)
            ->groupBy('employee_id')
            ->pluck('last_date', 'employee_id');

        return view('admin.attendances.department', compact(
            'department', 'employees', 'statsMap', 'lastDateMap', 'search', 'month', 'year'
        ));
    }

    public function employeeAttendance(Request $request, Department $department, Employee $employee): View
    {
        $filters = [
            'month'  => (int) $request->input('month', now()->month),
            'year'   => (int) $request->input('year',  now()->year),
            'status' => $request->input('status', ''),
        ];

        $attendancesQuery = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereMonth('attendance_date', $filters['month'])
            ->whereYear('attendance_date', $filters['year'])
            ->when($filters['status'], fn ($q) => $q->where('status', $filters['status']))
            ->orderBy('attendance_date');

        $attendances = $attendancesQuery->get();

        // Tải ca làm cho từng bản ghi
        $attendances->each(function ($att) {
            $att->employeeShift = EmployeeShift::with('shift')
                ->where('employee_id', $att->employee_id)
                ->whereDate('work_date', $att->attendance_date)
                ->first();
        });

        $summary = [
            'total'       => $attendances->count(),
            'present'     => $attendances->where('status', 'present')->count(),
            'late'        => $attendances->where('status', 'late')->count(),
            'absent'      => $attendances->where('status', 'absent')->count(),
            'leave'       => $attendances->where('status', 'leave')->count(),
            'total_hours' => round($attendances->sum('work_hours'), 1),
        ];

        $employee->load('department', 'position');

        return view('admin.attendances.employee', compact(
            'department', 'employee', 'attendances', 'summary', 'filters'
        ));
    }

    public function show(Attendance $attendance): View
    {
        $attendance->load([
            'employee.department',
            'employee.position',
        ]);
        $attendance->employeeShift = EmployeeShift::with('shift')
            ->where('employee_id', $attendance->employee_id)
            ->whereDate('work_date', $attendance->attendance_date)
            ->first();

        return view(
            'admin.attendances.show',
            compact('attendance')
        );
    }

    public function edit(Attendance $attendance): View
    {
        $attendance->load([
            'employee.department',
            'employee.position',
        ]);
        $attendance->employeeShift = EmployeeShift::with('shift')
            ->where('employee_id', $attendance->employee_id)
            ->whereDate('work_date', $attendance->attendance_date)
            ->first();

        return view(
            'admin.attendances.edit',
            compact('attendance')
        );
    }

    public function update(
        Request $request,
        Attendance $attendance
    ) {
        $data = $request->validate([
            'status' => ['required'],
            'check_in' => ['nullable'],
            'check_out' => ['nullable'],
            'work_hours' => ['nullable', 'numeric'],
        ]);

        $attendance->update($data);

        return redirect()
            ->route(
                'admin.attendances.show',
                $attendance
            )
            ->with(
                'success',
                'Cập nhật chấm công thành công'
            );
    }

    /**
     * @return array{
     *     attendances: \Illuminate\Contracts\Pagination\LengthAwarePaginator,
     *     stats: array{total: int, present: int, late: int, leave: int},
     *     filters: array{search: string, status: mixed, date: mixed}
     * }
     */
    private function buildListData(Request $request, ?int $departmentId = null): array
    {
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'status' => $request->input('status'),
            'date' => $request->input('date'),
        ];

        $scopedQuery = Attendance::query()
            ->when($departmentId, function ($query) use ($departmentId) {
                $query->whereHas(
                    'employee',
                    fn ($employeeQuery) => $employeeQuery->where('department_id', $departmentId)
                );
            });

        $stats = [
            'total' => (clone $scopedQuery)->count(),
            'present' => (clone $scopedQuery)->where('status', 'present')->count(),
            'late' => (clone $scopedQuery)->where('status', 'late')->count(),
            'leave' => (clone $scopedQuery)->where('status', 'leave')->count(),
        ];

        $attendances = (clone $scopedQuery)
            ->with([
                'employee.department',
                'employee.position',
            ])
            ->when($filters['search'], function ($query) use ($filters) {
                $query->whereHas('employee', function ($employee) use ($filters) {
                    $employee->where('employee_code', 'like', "%{$filters['search']}%")
                        ->orWhere('full_name', 'like', "%{$filters['search']}%");
                });
            })
            ->when($filters['status'], fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['date'], fn ($query) => $query->whereDate('attendance_date', $filters['date']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $attendances->getCollection()->transform(function ($attendance) {
            $attendance->employeeShift = EmployeeShift::with('shift')
                ->where('employee_id', $attendance->employee_id)
                ->whereDate('work_date', $attendance->attendance_date)
                ->first();

            return $attendance;
        });

        return compact('attendances', 'stats', 'filters');
    }
}
