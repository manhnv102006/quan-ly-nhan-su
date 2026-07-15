<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
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
        ]);
    }

    protected function department(Department $department, Request $request): View
    {
        $month = $request->input('month', now()->format('Y-m'));

        $employees = $department->employees()
            ->with(['position'])
            ->orderBy('full_name')
            ->get()
            ->map(function (Employee $employee) use ($month) {
                $count = Attendance::query()
                    ->where('employee_id', $employee->id)
                    ->where('attendance_date', 'like', $month.'%')
                    ->whereIn('status', ['present', 'late'])
                    ->count();

                $employee->work_days = $count;

                return $employee;
            });

        return view('accountant.attendance.department', [
            'department' => $department,
            'employees' => $employees,
            'month' => $month,
        ]);
    }
}
