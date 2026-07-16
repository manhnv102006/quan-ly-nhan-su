<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EarlyLeaveRequest extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'employee_id',
        'request_date',
        'leave_time',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'reject_reason',
    ];

    protected $casts = [
        'request_date' => 'date',
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'Đã duyệt',
            self::STATUS_REJECTED => 'Từ chối',
            default               => 'Chờ duyệt',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            self::STATUS_REJECTED => 'bg-rose-50 text-rose-700 border-rose-100',
            default               => 'bg-amber-50 text-amber-700 border-amber-100',
        };
    }
}
