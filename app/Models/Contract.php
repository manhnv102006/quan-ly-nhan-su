<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'employee_id',
        'department_id',
        'position_id',
        'contract_type_id',
        'contract_code',
        'start_date',
        'end_date',
        'salary',
        'allowance',
        'status',
        'file_path',
        'signed_date',
        'description',
        'note',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_date' => 'date',
        'salary' => 'decimal:2',
        'allowance' => 'decimal:2',
    ];

    /*
     * Relationships
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(ContractExtension::class)->orderByDesc('created_at');
    }

    public function terminations(): HasMany
    {
        return $this->hasMany(ContractTermination::class)->orderByDesc('created_at');
    }

    /*
     * Scopes & helpers
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeNotCancelled($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    public function scopeOverlapping($query, Carbon $start, ?Carbon $end = null, ?int $ignoreId = null)
    {
        $endDate = $end ?? Carbon::maxValue();

        return $query
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($q) use ($start, $endDate) {
                $q->whereBetween('start_date', [$start, $endDate])
                    ->orWhereBetween('end_date', [$start, $endDate])
                    ->orWhere(function ($q) use ($start, $endDate) {
                        $q->where('start_date', '<=', $start)
                            ->where(function ($q) use ($endDate) {
                                $q->whereNull('end_date')->orWhere('end_date', '>=', $endDate);
                            });
                    });
            });
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_ACTIVE], true);
    }

    public function isDeletable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_EXPIRED, self::STATUS_CANCELLED], true);
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_ACTIVE], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Đang hiệu lực',
            self::STATUS_EXPIRED => 'Hết hiệu lực',
            self::STATUS_DRAFT => 'Đang soạn',
            self::STATUS_CANCELLED => 'Đã hủy',
            default => 'Không xác định',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'badge text-bg-success',
            self::STATUS_EXPIRED => 'badge text-bg-warning',
            self::STATUS_CANCELLED => 'badge text-bg-danger',
            default => 'badge text-bg-secondary',
        };
    }
}
