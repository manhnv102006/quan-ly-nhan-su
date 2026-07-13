<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',

            'check_in' => 'datetime',
            'check_out' => 'datetime',

            'morning_check_in' => 'datetime',
            'morning_check_out' => 'datetime',

            'afternoon_check_in' => 'datetime',
            'afternoon_check_out' => 'datetime',

            'is_overtime' => 'boolean',

            'recognition_confidence' => 'float',
            'liveness_score' => 'float',
        ];
    }
    protected $fillable = [
        'employee_id',
        'shift_id',
        'attendance_date',

        'check_in',
        'check_out',

        'morning_check_in',
        'morning_check_out',
        'afternoon_check_in',
        'afternoon_check_out',

        'status',
        'work_hours',

        'check_in_method',
        'check_out_method',
        'recognition_confidence',
        'liveness_score',

        'late_minutes',
        'morning_late_minutes',
        'afternoon_late_minutes',

        'is_overtime',
        'overtime_hours',
    ];
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
    public function getLateTextAttribute(): string
    {
        return $this->late_minutes > 0
            ? $this->late_minutes . ' phút'
            : 'Đúng giờ';
    }

    public function getOvertimeTextAttribute(): string
    {
        return $this->is_overtime
            ? $this->overtime_hours . ' giờ'
            : 'Không';
    }

    public function getCheckInMethodLabelAttribute(): string
    {
        return match ($this->check_in_method) {
            'face' => 'Khuôn mặt',
            default => 'Thủ công',
        };
    }

    public function getCheckOutMethodLabelAttribute(): string
    {
        return match ($this->check_out_method) {
            'face' => 'Khuôn mặt',
            default => 'Thủ công',
        };
    }

    public function usesFaceRecognition(): bool
    {
        return $this->check_in_method === 'face' || $this->check_out_method === 'face';
    }

}
