<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveCarryOver extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_EXHAUSTED = 'exhausted';

    protected $fillable = [
        'employee_id',
        'source_year',
        'target_year',
        'days',
        'days_used',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'days' => 'float',
        'days_used' => 'float',
        'expires_at' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function remainingDays(): float
    {
        return max(0, (float) $this->days - (float) $this->days_used);
    }
}
