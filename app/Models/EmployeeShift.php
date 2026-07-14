<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeShift extends Model
{
    protected $fillable = [
        'employee_id',
        'shift_id',
        'work_date',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<EmployeeShift>  $query
     */
    public function scopeOverlappingTime($query, int $employeeId, string $workDate, string $startTime, string $endTime, ?int $ignoreShiftId = null)
    {
        return $query
            ->where('employee_id', $employeeId)
            ->whereDate('work_date', $workDate)
            ->when($ignoreShiftId, fn ($q) => $q->where('shift_id', '!=', $ignoreShiftId))
            ->whereHas('shift', fn ($shiftQuery) => $shiftQuery
                ->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime));
    }
}