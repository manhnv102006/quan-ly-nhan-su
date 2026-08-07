<?php

namespace App\Services;

use App\Models\EmployeeShift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EmployeeShiftScheduleService
{
    /** @var array<int, string> */
    private const WEEKDAY_SHORT = [
        0 => 'CN',
        1 => 'T2',
        2 => 'T3',
        3 => 'T4',
        4 => 'T5',
        5 => 'T6',
        6 => 'T7',
    ];

    /**
     * @return array{
     *     month: string,
     *     month_label: string,
     *     summaries: list<array<string, mixed>>,
     * }
     */
    public function summarizeMonth(string $workMonth, ?int $employeeId = null, ?int $shiftId = null): array
    {
        $start = Carbon::createFromFormat('Y-m', $workMonth)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $assignments = $this->baseQuery($employeeId, $shiftId)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('work_date')
            ->get();

        return [
            'month' => $workMonth,
            'month_label' => $start->locale('vi')->isoFormat('MMMM YYYY'),
            'summaries' => $this->buildSummaries($assignments),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function buildSummaries(Collection $assignments): array
    {
        if ($assignments->isEmpty()) {
            return [];
        }

        $summaries = [];

        $assignments
            ->groupBy(fn (EmployeeShift $row) => $row->employee_id.'-'.$row->shift_id)
            ->each(function (Collection $rows) use (&$summaries) {
                /** @var EmployeeShift $first */
                $first = $rows->first();
                $shift = $first->shift;
                $employee = $first->employee;

                if (! $shift || ! $employee) {
                    return;
                }

                $weekdays = $rows
                    ->map(fn (EmployeeShift $row) => Carbon::parse($row->work_date)->dayOfWeek)
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();

                $summaries[] = [
                    'employee_id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'employee_name' => $employee->full_name,
                    'department_name' => $employee->department?->department_name,
                    'shift_id' => $shift->id,
                    'shift_name' => $shift->shift_name,
                    'time_range' => Carbon::parse($shift->start_time)->format('H:i')
                        .' - '
                        .Carbon::parse($shift->end_time)->format('H:i'),
                    'days_count' => $rows->count(),
                    'weekday_pattern' => $this->weekdayPatternLabel($weekdays),
                    'weekdays' => $weekdays,
                ];
            });

        usort($summaries, fn (array $a, array $b) => [$a['employee_name'], $a['shift_name']] <=> [$b['employee_name'], $b['shift_name']]);

        return $summaries;
    }

    /**
     * @param  list<int>  $weekdays  Carbon day-of-week (0=CN … 6=T7)
     */
    public function weekdayPatternLabel(array $weekdays): string
    {
        $unique = array_values(array_unique($weekdays));
        sort($unique);

        if ($unique === [1, 3, 5]) {
            return 'T2, 4, 6';
        }

        if ($unique === [2, 4, 6]) {
            return 'T3, 5, 7';
        }

        if ($unique === [1, 2, 3, 4, 5, 6]) {
            return 'T2 – T7';
        }

        if ($unique === [0, 1, 2, 3, 4, 5, 6]) {
            return 'Hàng ngày';
        }

        return implode(', ', array_map(fn (int $day) => self::WEEKDAY_SHORT[$day] ?? '?', $unique));
    }

    /**
     * @return Builder<EmployeeShift>
     */
    public function filteredQuery(?int $employeeId, ?string $workMonth, ?int $shiftId): Builder
    {
        $query = $this->baseQuery($employeeId, $shiftId);

        if ($workMonth) {
            $start = Carbon::createFromFormat('Y-m', $workMonth)->startOfMonth();
            $query->whereBetween('work_date', [
                $start->toDateString(),
                $start->copy()->endOfMonth()->toDateString(),
            ]);
        }

        return $query;
    }

    /**
     * @return Builder<EmployeeShift>
     */
    private function baseQuery(?int $employeeId, ?int $shiftId): Builder
    {
        return EmployeeShift::query()
            ->with(['employee.department', 'shift'])
            ->whereHas('employee')
            ->whereHas('shift')
            ->when($employeeId, fn (Builder $q) => $q->where('employee_id', $employeeId))
            ->when($shiftId, fn (Builder $q) => $q->where('shift_id', $shiftId));
    }
}
