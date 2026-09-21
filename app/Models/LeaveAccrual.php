<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveAccrual extends Model
{
    public const SOURCE_SCHEDULED = 'scheduled';

    public const SOURCE_MANUAL = 'manual';

    protected $fillable = [
        'employee_id',
        'accrual_year',
        'accrual_month',
        'days',
        'source',
        'run_id',
        'accrued_at',
    ];

    protected $casts = [
        'days' => 'float',
        'accrued_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(LeaveAccrualRun::class, 'run_id');
    }
}
