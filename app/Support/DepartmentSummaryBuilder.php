<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Illuminate\Support\Collection;

class DepartmentSummaryBuilder
{
    /**
     * @return Collection<int, array{department: Department, stats: array{total: int, pending: int, approved: int}}>
     */
    public static function forLeave(): Collection
    {
        return self::departments()->map(function (Department $department) {
            $query = LeaveRequest::query()->whereHas(
                'employee',
                fn ($employeeQuery) => $employeeQuery->where('department_id', $department->id)
            );

            return [
                'department' => $department,
                'stats' => self::countByStatus($query, [
                    LeaveRequest::STATUS_PENDING,
                    LeaveRequest::STATUS_APPROVED,
                ]),
            ];
        });
    }

    /**
     * @return Collection<int, array{department: Department, stats: array{total: int, pending: int, approved: int}}>
     */
    public static function forOvertime(): Collection
    {
        return self::departments()->map(function (Department $department) {
            $query = OvertimeRequest::query()->whereHas(
                'employee',
                fn ($employeeQuery) => $employeeQuery->where('department_id', $department->id)
            );

            return [
                'department' => $department,
                'stats' => self::countByStatus($query, [
                    OvertimeRequest::STATUS_PENDING,
                    OvertimeRequest::STATUS_APPROVED,
                ]),
            ];
        });
    }

    /**
     * @return Collection<int, array{department: Department, stats: array{employee_count: int, work_days: int, late: int}}>
     */
    public static function forAttendance(?int $month = null, ?int $year = null): Collection
    {
        $month ??= (int) now()->month;
        $year ??= (int) now()->year;

        return self::departments()->map(function (Department $department) use ($month, $year) {
            $query = Attendance::query()
                ->whereHas('employee', fn ($employeeQuery) => $employeeQuery->where('department_id', $department->id))
                ->whereMonth('attendance_date', $month)
                ->whereYear('attendance_date', $year);

            return [
                'department' => $department,
                'stats' => self::attendanceStats($query),
            ];
        });
    }

    /**
     * @return Collection<int, array{department: Department, stats: array{employee_count: int, work_days: int, late: int}}>
     */
    public static function forAttendanceManagement(): Collection
    {
        return self::departments()->map(function (Department $department) {
            $query = Attendance::query()
                ->whereHas('employee', fn ($employeeQuery) => $employeeQuery->where('department_id', $department->id));

            return [
                'department' => $department,
                'stats' => self::attendanceStats($query),
            ];
        });
    }

    /**
     * @return Collection<int, Department>
     */
    private static function departments(): Collection
    {
        return Department::query()
            ->where('status', 'active')
            ->orderBy('department_name')
            ->get(['id', 'department_code', 'department_name']);
    }

    /**
     * @return Collection<int, array{department: Department, stats: array{total_salary: float, employee_count: int}}>
     */
    public static function forPayrollPeriod(\App\Models\PayrollPeriod $period): Collection
    {
        return self::departments()->map(function (Department $department) use ($period) {
            $query = \App\Models\Payroll::query()
                ->where('payroll_period_id', $period->id)
                ->whereHas('employee', fn ($employeeQuery) => $employeeQuery->where('department_id', $department->id));

            $deptPayrolls = (clone $query)->get();

            if ($deptPayrolls->isEmpty()) {
                $statusLabel = 'Tạm tính';
            } else {
                $statuses = $deptPayrolls->pluck('status')->unique();
                if ($statuses->contains('closed')) {
                    $statusLabel = 'Đã đóng';
                } elseif ($statuses->contains('paid')) {
                    $statusLabel = 'Đã chi trả';
                } elseif ($statuses->contains('approved')) {
                    $statusLabel = 'Đã duyệt';
                } else {
                    $statusLabel = 'Đã tính';
                }
            }

            return [
                'department' => $department,
                'stats' => [
                    'employee_count' => $deptPayrolls->count(),
                    'total_salary' => $deptPayrolls->sum('total_salary'),
                    'status_label' => $statusLabel,
                ],
            ];
        });
    }

    /**
     * @param  list<string>  $statuses
     * @return array{total: int, pending: int, approved: int}
     */
    private static function countByStatus($query, array $statuses): array
    {
        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', $statuses[0])->count(),
            'approved' => (clone $query)->where('status', $statuses[1])->count(),
        ];
    }

    /**
     * @return array{employee_count: int, work_days: int, late: int}
     */
    private static function attendanceStats($query): array
    {
        return [
            'employee_count' => (clone $query)->distinct()->count('employee_id'),
            'work_days' => (clone $query)->whereIn('status', ['present', 'late'])->count(),
            'late' => (clone $query)->where('status', 'late')->count(),
        ];
    }
}
