<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryAdvance extends Model
{
    public const MIN_AMOUNT = 5_000_000;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_SETTLED = 'settled';

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Chờ duyệt',
        self::STATUS_APPROVED => 'Đã duyệt',
        self::STATUS_REJECTED => 'Từ chối',
        self::STATUS_PARTIAL => 'Đang trừ dần',
        self::STATUS_SETTLED => 'Đã hoàn trả',
    ];

    public const STATUS_BADGES = [
        self::STATUS_PENDING => 'bg-amber-100 text-amber-800',
        self::STATUS_APPROVED => 'bg-sky-100 text-sky-800',
        self::STATUS_REJECTED => 'bg-rose-100 text-rose-800',
        self::STATUS_PARTIAL => 'bg-orange-100 text-orange-800',
        self::STATUS_SETTLED => 'bg-emerald-100 text-emerald-800',
    ];

    protected $fillable = [
        'advance_code',
        'employee_id',
        'amount',
        'amount_settled',
        'request_date',
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_settled' => 'decimal:2',
            'request_date' => 'date',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(SalaryAdvanceDeduction::class);
    }

    public function remainingBalance(): float
    {
        return max(0, (float) $this->amount - (float) $this->amount_settled);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusBadgeClass(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'bg-slate-100 text-slate-600';
    }

    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeRejected(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeDeducted(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_PARTIAL], true)
            && $this->remainingBalance() > 0;
    }

    public static function generateCode(): string
    {
        $prefix = 'TU'.now()->format('ym');
        $last = self::query()
            ->where('advance_code', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('advance_code');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
