<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollPenaltyDetail extends Model
{
    public const TYPE_LATE = 'late';

    public const TYPE_EARLY = 'early';

    public const TYPE_MISSING_CHECKOUT = 'missing_checkout';

    public const TYPE_UNPAID_LEAVE = 'unpaid_leave';

    public const TYPE_MANUAL = 'manual';

    public const TYPE_LEGACY = 'legacy';

    protected $fillable = [
        'payroll_id',
        'type',
        'label',
        'amount',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }
}
