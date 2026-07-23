<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxDependent extends Model
{
    /** Giá trị dự phòng khi chưa có bản ghi trong bảng tax_policies. */
    public const DEFAULT_MONTHLY_DEDUCTION = 6_200_000;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Chờ duyệt',
        self::STATUS_APPROVED => 'Đã duyệt',
        self::STATUS_REJECTED => 'Từ chối',
    ];

    public const STATUS_BADGES = [
        self::STATUS_PENDING => 'bg-amber-100 text-amber-800',
        self::STATUS_APPROVED => 'bg-emerald-100 text-emerald-800',
        self::STATUS_REJECTED => 'bg-rose-100 text-rose-800',
    ];

    public const RELATIONSHIP_LABELS = [
        'child' => 'Con',
        'spouse' => 'Vợ/Chồng',
        'parent' => 'Cha/Mẹ',
        'other' => 'Khác',
    ];

    public const CHILD_CATEGORY_LABELS = [
        'minor' => 'Con dưới 18 tuổi',
        'student' => 'Con trên 18 tuổi (đang học ĐH)',
    ];

    protected $fillable = [
        'employee_id',
        'status',
        'full_name',
        'relationship',
        'child_category',
        'date_of_birth',
        'id_number',
        'monthly_deduction',
        'start_date',
        'end_date',
        'is_active',
        'note',
        'requested_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'monthly_deduction' => 'decimal:2',
            'is_active' => 'boolean',
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

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaxDependentDocument::class);
    }

    public function relationshipLabel(): string
    {
        $base = self::RELATIONSHIP_LABELS[$this->relationship] ?? $this->relationship;
        if ($this->relationship === 'child' && $this->child_category) {
            $sub = self::CHILD_CATEGORY_LABELS[$this->child_category] ?? null;
            if ($sub) {
                return $base.' — '.$sub;
            }
        }

        return $base;
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

    public function countsForTaxDeduction(?\Carbon\Carbon $date = null): bool
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return false;
        }

        return $this->isEffectiveOn($date ?? now());
    }

    public function isEffectiveOn(\Carbon\Carbon $date): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->start_date && $this->start_date->gt($date)) {
            return false;
        }

        if ($this->end_date && $this->end_date->lt($date)) {
            return false;
        }

        return true;
    }
}
