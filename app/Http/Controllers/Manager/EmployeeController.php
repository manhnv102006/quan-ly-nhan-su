<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Services\AccountantAttendanceService;
use App\Services\AttendanceHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $managerProfile = Employee::query()
            ->with(['department', 'position'])
            ->where('user_id', Auth::id())
            ->first();

        $department = $this->managedDepartment($managerProfile);
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $employees = Employee::query()
            ->with(['department', 'position', 'user'])
            ->when($department, fn ($query) => $query->where('department_id', $department->id))
            ->when(! $department, fn ($query) => $query->whereRaw('1 = 0'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('employee_code', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, Employee::selectableStatuses(), true), fn ($query) => $query->where('status', $status))
            ->orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'inactive' THEN 1 WHEN 'on_leave' THEN 2 ELSE 3 END")
            ->orderBy('full_name')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => $department ? Employee::where('department_id', $department->id)->count() : 0,
            'active' => $department ? Employee::where('department_id', $department->id)->where('status', Employee::STATUS_ACTIVE)->count() : 0,
            'inactive' => $department ? Employee::where('department_id', $department->id)->where('status', Employee::STATUS_INACTIVE)->count() : 0,
            'on_leave' => $department ? Employee::where('department_id', $department->id)->where('status', Employee::STATUS_ON_LEAVE)->count() : 0,
            'resigned' => $department ? Employee::where('department_id', $department->id)->where('status', Employee::STATUS_RESIGNED)->count() : 0,
        ];

        return view('manager.employees.index', compact(
            'managerProfile',
            'department',
            'employees',
            'stats',
            'search',
            'status',
        ));
    }

    public function show(Employee $employee): View
    {
        $managerProfile = Employee::query()
            ->with(['department', 'position'])
            ->where('user_id', Auth::id())
            ->first();

        $department = $this->managedDepartment($managerProfile);

        if (! $department || (int) $employee->department_id !== (int) $department->id) {
            abort(403, 'Bạn chỉ được xem nhân viên thuộc phòng ban mình quản lý.');
        }

        $employee->load(['department', 'position', 'user']);

        $attendances = $employee->attendances()
            ->with('shift')
            ->latest('attendance_date')
            ->limit(8)
            ->get();

        $recentSessionRows = app(AttendanceHistoryService::class)->sessionRows($attendances);

        $leaveRequests = $employee->leaveRequests()
            ->latest()
            ->limit(6)
            ->get();

        $kpis = $employee->employeeKpis()
            ->with('kpi')
            ->latest()
            ->limit(6)
            ->get();

        return view('manager.employees.show', compact(
            'managerProfile',
            'department',
            'employee',
            'attendances',
            'recentSessionRows',
            'leaveRequests',
            'kpis',
        ));
    }

    public function attendance(
        Request $request,
        Employee $employee,
        AccountantAttendanceService $accountantAttendance,
        AttendanceHistoryService $historyService,
    ): View {
        $managerProfile = Employee::query()
            ->with(['department', 'position'])
            ->where('user_id', Auth::id())
            ->first();

        $department = $this->managedDepartment($managerProfile);

        if (! $department || (int) $employee->department_id !== (int) $department->id) {
            abort(403, 'Bạn chỉ được xem nhân viên thuộc phòng ban mình quản lý.');
        }

        $filters = [
            'month'  => (int) $request->input('month', now()->month),
            'year'   => (int) $request->input('year', now()->year),
            'status' => $request->input('status', ''),
        ];

        $attendances = $accountantAttendance->attendancesForEmployee(
            $employee,
            $filters['year'],
            $filters['month'],
            $filters['status'] ?: null,
        );

        $sessionRows = $historyService->sessionRows($attendances);
        $summary = $historyService->summaryFromRows($sessionRows, $attendances);

        $employee->load(['department', 'position']);

        return view('manager.employees.attendance', compact(
            'managerProfile',
            'department',
            'employee',
            'attendances',
            'sessionRows',
            'summary',
            'filters',
        ));
    }

    private function managedDepartment(?Employee $managerProfile): ?Department
    {
        if (! $managerProfile) {
            return null;
        }

        return Department::query()
            ->where('manager_id', $managerProfile->id)
            ->first()
            ?? $managerProfile->department;
    }
}
