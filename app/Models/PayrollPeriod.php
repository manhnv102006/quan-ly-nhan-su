<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    protected $fillable = [
        'name',
        'month',
        'year',
        'start_date',
        'end_date',
        'status',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isCalculated(): bool
    {
        return $this->status === 'calculated';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
