<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeCodeService
{
    public function prefixFor(Department $department): string
    {
        $name = trim((string) $department->department_name);
        $name = preg_replace('/^phòng\s+/iu', '', $name) ?? $name;
        $ascii = Str::ascii($name);
        $words = preg_split('/\s+/', $ascii, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $initials = '';
        foreach ($words as $word) {
            $clean = preg_replace('/[^A-Za-z0-9]/', '', $word) ?? '';
            if ($clean === '') {
                continue;
            }
            $initials .= strtoupper(substr($clean, 0, 1));
        }

        if (strlen($initials) < 2) {
            $fromCode = strtoupper(preg_replace('/[^A-Z0-9]/', '', Str::ascii((string) $department->department_code)) ?? '');
            $initials = $fromCode !== '' ? $fromCode : ($initials !== '' ? $initials : 'NV');
        }

        return substr($initials, 0, 12);
    }

    public function nextCode(int $departmentId): string
    {
        $department = Department::query()->findOrFail($departmentId);
        $prefix = $this->prefixFor($department);
        $next = $this->highestSequence($prefix) + 1;

        return $this->format($prefix, $next);
    }

    /**
     * Giữ mã cũ nếu vẫn đúng phòng ban. Đổi phòng thì cấp mã mới của phòng đích.
     */
    public function codeForSave(?Employee $employee, int $departmentId): string
    {
        if ($employee && (int) $employee->department_id === $departmentId) {
            $department = $employee->relationLoaded('department')
                ? $employee->department
                : Department::query()->find($departmentId);

            if ($department && str_starts_with($employee->employee_code, $this->prefixFor($department))) {
                return $employee->employee_code;
            }
        }

        return $this->nextCode($departmentId);
    }

    /**
     * @return list<array{id: int, old: string, new: string}>
     */
    public function reassignAll(): array
    {
        return DB::transaction(function () {
            $employees = Employee::withTrashed()->orderBy('id')->get();
            $oldCodes = $employees->mapWithKeys(
                fn (Employee $employee) => [$employee->id => $employee->employee_code]
            );

            foreach ($employees as $employee) {
                $employee->update(['employee_code' => 'TMP'.$employee->id]);
            }

            $changes = [];

            $grouped = Employee::withTrashed()
                ->orderBy('id')
                ->get()
                ->groupBy(fn (Employee $employee) => (int) ($employee->department_id ?? 0));

            foreach ($grouped as $departmentId => $group) {
                $prefix = $departmentId
                    ? $this->prefixFor(Department::query()->findOrFail($departmentId))
                    : 'NV';

                $sequence = 1;
                foreach ($group as $employee) {
                    $new = $this->format($prefix, $sequence);
                    $employee->update(['employee_code' => $new]);
                    $changes[] = [
                        'id' => $employee->id,
                        'old' => (string) ($oldCodes[$employee->id] ?? ''),
                        'new' => $new,
                    ];
                    $sequence++;
                }
            }

            return $changes;
        });
    }

    protected function highestSequence(string $prefix): int
    {
        $pattern = '/^'.preg_quote($prefix, '/').'(\d+)$/';
        $max = 0;

        $codes = Employee::withTrashed()
            ->where('employee_code', 'like', $prefix.'%')
            ->pluck('employee_code');

        foreach ($codes as $code) {
            $max = max($max, $this->sequenceOf((string) $code, $pattern));
        }

        EmployeeHistory::query()
            ->whereNotNull('changes')
            ->select(['changes'])
            ->orderBy('id')
            ->each(function (EmployeeHistory $history) use (&$max, $pattern) {
                foreach ($history->changes ?? [] as $change) {
                    if (($change['field'] ?? null) !== 'employee_code') {
                        continue;
                    }
                    $max = max(
                        $max,
                        $this->sequenceOf((string) ($change['old'] ?? ''), $pattern),
                        $this->sequenceOf((string) ($change['new'] ?? ''), $pattern),
                    );
                }
            });

        return $max;
    }

    protected function sequenceOf(string $code, string $pattern): int
    {
        if (preg_match($pattern, $code, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    protected function format(string $prefix, int $sequence): string
    {
        $width = max(3, strlen((string) $sequence));

        return $prefix.str_pad((string) $sequence, $width, '0', STR_PAD_LEFT);
    }
}
