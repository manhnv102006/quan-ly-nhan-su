<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OvertimeRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Chờ duyệt',
        self::STATUS_APPROVED => 'Đã duyệt',
        self::STATUS_REJECTED => 'Từ chối',
        self::STATUS_COMPLETED => 'Hoàn thành',
    ];

    public const STATUS_BADGE_CLASSES = [
        self::STATUS_PENDING => 'text-bg-warning',
        self::STATUS_APPROVED => 'text-bg-success',
        self::STATUS_REJECTED => 'text-bg-danger',
        self::STATUS_COMPLETED => 'text-bg-primary',
    ];

    public const STATUS_TAILWIND_CLASSES = [
        self::STATUS_PENDING => 'bg-amber-50 text-amber-700 border-amber-100',
        self::STATUS_APPROVED => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        self::STATUS_REJECTED => 'bg-rose-50 text-rose-700 border-rose-100',
        self::STATUS_COMPLETED => 'bg-sky-50 text-sky-700 border-sky-100',
    ];

    protected $fillable = [
        'employee_id',
        'work_date',
        'start_time',
        'end_time',
        'total_hours',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'reject_reason',
        'actual_check_in',
        'actual_check_out',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'approved_at' => 'datetime',
            'actual_check_in' => 'datetime',
            'actual_check_out' => 'datetime',
            'total_hours' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OvertimeRequestHistory::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function scopeOverlappingTime($query, int $employeeId, string $workDate, string $startTime, string $endTime, ?int $ignoreId = null)
    {
        return $query
            ->where('employee_id', $employeeId)
            ->whereDate('work_date', $workDate)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId));
    }

    /**
     * @param  Builder<OvertimeRequest>  $query
     */
    public function scopeForManager(Builder $query, Employee $manager): Builder
    {
        return $query->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->managedByManager($manager));
    }

    /**
     * @param  Builder<OvertimeRequest>  $query
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(! empty($filters['search']), function (Builder $q) use ($filters) {
                $keyword = trim((string) $filters['search']);
                $q->whereHas('employee', function (Builder $employeeQuery) use ($keyword) {
                    $employeeQuery->where('full_name', 'like', '%'.$keyword.'%')
                        ->orWhere('employee_code', 'like', '%'.$keyword.'%');
                });
            })
            ->when(! empty($filters['employee_name']), function (Builder $q) use ($filters) {
                $name = trim((string) $filters['employee_name']);
                $q->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->where('full_name', 'like', '%'.$name.'%'));
            })
            ->when(! empty($filters['employee_code']), function (Builder $q) use ($filters) {
                $code = trim((string) $filters['employee_code']);
                $q->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->where('employee_code', 'like', '%'.$code.'%'));
            })
            ->when(! empty($filters['status']), fn (Builder $q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['work_date']), fn (Builder $q) => $q->whereDate('work_date', $filters['work_date']))
            ->when(! empty($filters['work_date_from']), fn (Builder $q) => $q->whereDate('work_date', '>=', $filters['work_date_from']))
            ->when(! empty($filters['work_date_to']), fn (Builder $q) => $q->whereDate('work_date', '<=', $filters['work_date_to']))
            ->when(! empty($filters['employee_id']), fn (Builder $q) => $q->where('employee_id', $filters['employee_id']));
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusBadgeClass(): string
    {
        return self::STATUS_BADGE_CLASSES[$this->status] ?? 'text-bg-secondary';
    }

    public function statusTailwindClass(): string
    {
        return self::STATUS_TAILWIND_CLASSES[$this->status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
    }
}