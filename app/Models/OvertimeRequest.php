<?php

namespace App\Models;

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
        self::STATUS_PENDING => 'Pending',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_COMPLETED => 'Completed',
    ];
    public const STATUS_BADGE_CLASSES = [
        self::STATUS_PENDING => 'text-bg-warning',
        self::STATUS_APPROVED => 'text-bg-success',
        self::STATUS_REJECTED => 'text-bg-danger',
        self::STATUS_COMPLETED => 'text-bg-primary',
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
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'approved_at' => 'datetime',
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

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $keyword = trim((string) $filters['search']);
                $q->whereHas('employee', function ($employeeQuery) use ($keyword) {
                    $employeeQuery->where('full_name', 'like', '%'.$keyword.'%')
                        ->orWhere('employee_code', 'like', '%'.$keyword.'%');
                });
            })
            ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['work_date']), fn ($q) => $q->whereDate('work_date', $filters['work_date']))
            ->when(! empty($filters['employee_id']), fn ($q) => $q->where('employee_id', $filters['employee_id']))
            ->when(! empty($filters['department_id']), function ($q) use ($filters) {
                $q->whereHas('employee', fn ($employeeQuery) => $employeeQuery->where('department_id', $filters['department_id']));
            });
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusBadgeClass(): string
    {
        return self::STATUS_BADGE_CLASSES[$this->status] ?? 'text-bg-secondary';
    }
}