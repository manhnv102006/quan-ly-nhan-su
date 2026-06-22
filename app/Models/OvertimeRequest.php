<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'overtime_date',
        'start_time',
        'end_time',
        'reason',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'overtime_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}