<?php

namespace App\Services;

use App\Models\EmployeeShift;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use InvalidArgumentException;

class EmployeeShiftAssignmentService
{
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

        foreach (array_chunk($rows, 500) as $chunk) {
            EmployeeShift::upsert(
                $chunk,
                ['employee_id', 'work_date'],
                ['shift_id', 'updated_at'],
            );
        }

        return count($rows);
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
