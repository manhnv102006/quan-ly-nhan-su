<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Support\DepartmentSummaryBuilder;
use Illuminate\Http\Request;
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
        return view('admin.attendances.department', [
            ...$this->buildListData($request, $department->id),
            'selectedDepartment' => $department,
            'scopeLabel' => $department->department_name,
            'showDepartmentColumn' => false,
        ]);
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
