<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveAccrualRun extends Model
{
    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'accrual_year',
        'accrual_month',
        'status',
        'accruals_created',
        'accruals_skipped',
        'employees_ineligible',
        'message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function accruals(): HasMany
    {
        return $this->hasMany(LeaveAccrual::class, 'run_id');
    }
}
