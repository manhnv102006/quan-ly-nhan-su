<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeShift;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccountantAttendanceService
{
    /** @return array{0: int, 1: int} */
    public function parseMonth(string $month): array
    {
        [$year, $monthNum] = array_pad(explode('-', $month), 2, now()->format('m'));

        return [(int) $year, (int) $monthNum];
    }

    /**
     * @param  array<int>  $employeeIds
     * @return Collection<int, object>
     */
    public function statsForEmployees(array $employeeIds, int $year, int $month): Collection
    {
        if ($employeeIds === []) {
            return collect();
        }

        return DB::table('attendances')
            ->selectRaw('employee_id,
                COUNT(*) as total,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = "leave" THEN 1 ELSE 0 END) as `leave`,
                SUM(COALESCE(work_hours, 0)) as total_hours,
                SUM(COALESCE(overtime_hours, 0)) as overtime_hours,
                SUM(COALESCE(late_minutes, 0)) as late_minutes')
            ->whereIn('employee_id', $employeeIds)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');
    }

    public function payableDays(?object $stats): int
    {
        if (! $stats) {
            return 0;
        }

        return (int) $stats->present + (int) $stats->late;
    }

    /**
     * @return array<string, int|float>
     */
    public function summaryFromCollection(Collection $attendances): array
    {
        return [
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'leave' => $attendances->where('status', 'leave')->count(),
            'payable_days' => $attendances->whereIn('status', ['present', 'late'])->count(),
            'total_hours' => round((float) $attendances->sum('work_hours'), 1),
            'overtime_hours' => round((float) $attendances->sum('overtime_hours'), 1),
            'late_minutes' => (int) $attendances->sum('late_minutes'),
        ];
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function attendancesForEmployee(Employee $employee, int $year, int $month, ?string $status = null): Collection
    {
        $attendances = Attendance::query()
            ->with('shift')
            ->where('employee_id', $employee->id)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('attendance_date')
            ->get();

        $shiftMap = EmployeeShift::query()
            ->with('shift')
            ->where('employee_id', $employee->id)
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->get()
            ->keyBy(fn ($row) => $row->work_date->format('Y-m-d'));

        return $attendances->each(function (Attendance $attendance) use ($shiftMap) {
            $key = $attendance->attendance_date->format('Y-m-d');
            $attendance->setRelation('employeeShift', $shiftMap->get($key));
        });
    }
}
