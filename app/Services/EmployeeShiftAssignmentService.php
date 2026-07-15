<?php

namespace App\Services;

use App\Models\EmployeeShift;
use App\Models\Shift;
use App\Support\ShiftTimeRange;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use InvalidArgumentException;

class EmployeeShiftAssignmentService
{
    /**
     * @param  list<int>  $employeeIds
     * @return list<string>
     */
    public function findOverlappingConflicts(array $validated, array $employeeIds): array
    {
        if ($employeeIds === []) {
            return [];
        }

        $shift = Shift::query()->findOrFail((int) $validated['shift_id']);
        $dates = $this->resolveDates($validated);
        $newStart = Carbon::parse($shift->start_time)->format('H:i:s');
        $newEnd = Carbon::parse($shift->end_time)->format('H:i:s');
        $conflicts = [];

        foreach ($employeeIds as $employeeId) {
            foreach ($dates as $date) {
                $existingAssignments = EmployeeShift::query()
                    ->where('employee_id', $employeeId)
                    ->whereDate('work_date', $date)
                    ->where('shift_id', '!=', $shift->id)
                    ->with(['employee', 'shift'])
                    ->get();

                foreach ($existingAssignments as $existing) {
                    if (! $existing->employee || ! $existing->shift) {
                        continue;
                    }

                    $existingStart = Carbon::parse($existing->shift->start_time)->format('H:i:s');
                    $existingEnd = Carbon::parse($existing->shift->end_time)->format('H:i:s');

                    if (! ShiftTimeRange::overlaps($newStart, $newEnd, $existingStart, $existingEnd)) {
                        continue;
                    }

                    $conflicts[] = sprintf(
                        '%s (%s) đã có ca "%s" (%s - %s) trùng giờ với ca "%s" ngày %s.',
                        $existing->employee->full_name,
                        $existing->employee->employee_code,
                        $existing->shift->shift_name,
                        Carbon::parse($existing->shift->start_time)->format('H:i'),
                        Carbon::parse($existing->shift->end_time)->format('H:i'),
                        $shift->shift_name,
                        Carbon::parse($date)->format('d/m/Y'),
                    );
                }
            }
        }

        return $conflicts;
    }

    /**
     * @param  list<int>  $employeeIds
     */
    public function assign(array $validated, array $employeeIds): int
    {
        if ($employeeIds === []) {
            return 0;
        }

        $dates = $this->resolveDates($validated);
        $shiftId = (int) $validated['shift_id'];
        $now = now();
        $rows = [];

        foreach ($employeeIds as $employeeId) {
            foreach ($dates as $date) {
                $rows[] = [
                    'employee_id' => $employeeId,
                    'work_date' => $date,
                    'shift_id' => $shiftId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $assignedCount = 0;

        foreach (array_chunk($rows, 500) as $chunk) {
            foreach ($chunk as $row) {
                $created = EmployeeShift::query()->firstOrCreate(
                    [
                        'employee_id' => $row['employee_id'],
                        'work_date' => $row['work_date'],
                        'shift_id' => $row['shift_id'],
                    ],
                    [
                        'created_at' => $row['created_at'],
                        'updated_at' => $row['updated_at'],
                    ],
                );

                if ($created->wasRecentlyCreated) {
                    $assignedCount++;
                }
            }
        }

        return $assignedCount;
    }

    /**
     * @return list<string>
     */
    public function resolveDates(array $validated): array
    {
        $mode = $validated['period_mode'] ?? 'single';

        return match ($mode) {
            'single' => [Carbon::parse($validated['work_date'])->toDateString()],
            'month' => $this->datesInMonth((string) $validated['work_month']),
            'year' => $this->datesInYear((int) $validated['work_year']),
            'range' => $this->datesInRange(
                (string) $validated['start_date'],
                (string) $validated['end_date'],
            ),
            default => throw new InvalidArgumentException('Invalid period mode.'),
        };
    }

    public function countDates(array $validated): int
    {
        return count($this->resolveDates($validated));
    }

    /**
     * @return list<string>
     */
    private function datesInMonth(string $workMonth): array
    {
        $start = Carbon::createFromFormat('Y-m', $workMonth)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return $this->collectDates($start, $end);
    }

    /**
     * @return list<string>
     */
    private function datesInYear(int $workYear): array
    {
        $start = Carbon::createFromDate($workYear, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($workYear, 12, 31)->startOfDay();

        return $this->collectDates($start, $end);
    }

    /**
     * @return list<string>
     */
    private function datesInRange(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        return $this->collectDates($start, $end);
    }

    /**
     * @return list<string>
     */
    private function collectDates(Carbon $start, Carbon $end): array
    {
        $dates = [];

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $dates[] = $date->toDateString();
        }

        return $dates;
    }
}
