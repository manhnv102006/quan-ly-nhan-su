<?php

namespace App\Models;

use App\Models\Attendance;
use App\Support\ShiftTimeRange;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'shift_name',
        'start_time',
        'end_time'
    ];
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:H:i:s',
            'end_time' => 'datetime:H:i:s',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
    public function employeeShifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class);
    }

    public function overlapsTime(string $startTime, string $endTime): bool
    {
        $shiftStart = $this->start_time->format('H:i:s');
        $shiftEnd = $this->end_time->format('H:i:s');

        return ShiftTimeRange::overlaps($shiftStart, $shiftEnd, $startTime, $endTime);
    }
}
