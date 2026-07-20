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
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REPLACED = 'replaced';
    public const STATUS_TERMINATED = 'terminated';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Đang soạn',
        self::STATUS_PENDING => 'Chờ hiệu lực',
        self::STATUS_ACTIVE => 'Còn hiệu lực',
        self::STATUS_EXPIRED => 'Đã hết hạn',
        self::STATUS_REPLACED => 'Đã thay thế',
        self::STATUS_TERMINATED => 'Đã chấm dứt',
        self::STATUS_CANCELLED => 'Đã hủy',
    ];

    protected $fillable = [
        'employee_id',
        'previous_contract_id',
        'department_id',
        'position_id',
        'contract_type_id',
        'renewal_count',
        'contract_code',
        'start_date',
        'end_date',
        'actual_end_date',
        'salary',
        'allowance',
        'allowance_meal',
        'allowance_phone',
        'allowance_fuel',
        'allowance_position',
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
        'actual_end_date' => 'date',
        'signed_date' => 'date',
        'renewal_count' => 'integer',
        'salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'allowance_meal' => 'decimal:2',
        'allowance_phone' => 'decimal:2',
        'allowance_fuel' => 'decimal:2',
        'allowance_position' => 'decimal:2',
    ];

    /*
     * Relationships
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function previousContract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'previous_contract_id');
    }

    public function successorContracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'previous_contract_id');
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

    public function contractAllowances(): HasMany
    {
        return $this->hasMany(ContractAllowance::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ContractActivityLog::class)->orderByDesc('created_at');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ContractHistory::class)->orderByDesc('created_at');
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

    public function scopeCurrentlyEffective($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_PENDING]);
    }

    public function scopeOccupyingPeriod($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_ACTIVE,
        ]);
    }

    public static function farFutureDate(): Carbon
    {
        return Carbon::parse('9999-12-31')->endOfDay();
    }

    public function scopeOverlapping($query, Carbon $start, ?Carbon $end = null, ?int $ignoreId = null)
    {
        $endDate = $end ?? self::farFutureDate();

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
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_ACTIVE], true);
    }

    public function getSalaryBaseAttribute(): string
    {
        return (string) $this->salary;
    }

    public function getBasicSalaryAttribute(): float
    {
        return (float) $this->salary;
    }

    public function isDeletable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_EXPIRED, self::STATUS_CANCELLED], true);
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_ACTIVE], true);
    }

    public function canBeExtended(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isFixedTermRenewalBlocked(): bool
    {
        $this->loadMissing('contractType');

        return $this->contractType?->category === ContractType::CATEGORY_FIXED
            && (int) $this->renewal_count >= 1;
    }

    public static function fixedTermRenewalBlockedMessage(): string
    {
        return 'Hợp đồng đã gia hạn 1 lần, theo luật phải chuyển sang Không xác định thời hạn';
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === self::STATUS_ACTIVE && $this->isExpiringSoon()) {
            return 'Sắp hết hạn';
        }

        return self::STATUS_LABELS[$this->status] ?? 'Không xác định';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->status === self::STATUS_ACTIVE && $this->isExpiringSoon()) {
            return 'badge text-bg-warning';
        }

        return match ($this->status) {
            self::STATUS_ACTIVE => 'badge text-bg-success',
            self::STATUS_PENDING => 'badge text-bg-info',
            self::STATUS_EXPIRED => 'badge text-bg-warning',
            self::STATUS_REPLACED => 'badge text-bg-secondary',
            self::STATUS_TERMINATED => 'badge text-bg-danger',
            self::STATUS_CANCELLED => 'badge text-bg-secondary',
            default => 'badge text-bg-secondary',
        };
    }

    public function getStatusTailwindClassAttribute(): string
    {
        if ($this->status === self::STATUS_ACTIVE && $this->isExpiringSoon()) {
            return 'bg-amber-50 text-amber-700 border-amber-100';
        }

        return match ($this->status) {
            self::STATUS_ACTIVE => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            self::STATUS_PENDING => 'bg-sky-50 text-sky-700 border-sky-100',
            self::STATUS_EXPIRED => 'bg-amber-50 text-amber-700 border-amber-100',
            self::STATUS_DRAFT => 'bg-slate-100 text-slate-700 border-slate-200',
            self::STATUS_REPLACED => 'bg-slate-100 text-slate-600 border-slate-200',
            self::STATUS_TERMINATED => 'bg-rose-50 text-rose-700 border-rose-100',
            self::STATUS_CANCELLED => 'bg-slate-100 text-slate-600 border-slate-200',
            default => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }

    public function getLatestTerminationAttribute(): ?ContractTermination
    {
        return $this->relationLoaded('terminations')
            ? $this->terminations->first()
            : $this->terminations()->first();
    }

    public function getDisplayDepartmentNameAttribute(): string
    {
        return $this->department?->department_name
            ?? $this->employee?->department?->department_name
            ?? '—';
    }

    public function getDisplayPositionNameAttribute(): string
    {
        return $this->position?->position_name
            ?? $this->employee?->position?->position_name
            ?? '—';
    }

    public function isExpiringSoon(int $withinDays = 30): bool
    {
        if ($this->status !== self::STATUS_ACTIVE || ! $this->end_date) {
            return false;
        }

        return $this->end_date->isFuture()
            && $this->end_date->lte(Carbon::today()->addDays($withinDays));
    }
}
