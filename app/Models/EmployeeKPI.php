<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeKPI extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_NOT_COMPLETED = 'not_completed';

    protected $table = 'employee_kpis';

    protected $fillable = [
        'assignment_id',
        'employee_id',
        'kpi_id',
        'target',
        'deadline',
        'progress',
        'status',
        'score',
        'leader_score',
        'leader_review',
        'comment',
        'review',
        'assigned_by',
    ];

    protected $casts = [
        'deadline' => 'date',
        'progress' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (EmployeeKPI $employeeKpi) {
            $employeeKpi->status ??= self::STATUS_PENDING;
        });

        static::saving(function (EmployeeKPI $employeeKpi) {
            if ($employeeKpi->isOverdue() && $employeeKpi->status !== self::STATUS_COMPLETED) {
                $employeeKpi->status = self::STATUS_NOT_COMPLETED;
            }
        });
    }

    public static function markOverdueAsNotCompleted(): int
    {
        return self::query()
            ->whereNotNull('deadline')
            ->whereDate('deadline', '<', today()->toDateString())
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS])
            ->update(['status' => self::STATUS_NOT_COMPLETED]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Chờ bắt đầu',
            self::STATUS_IN_PROGRESS => 'Đang thực hiện',
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_NOT_COMPLETED => 'Không hoàn thành',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_IN_PROGRESS => 'badge-info',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_NOT_COMPLETED => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function isOverdue(): bool
    {
        return $this->deadline !== null && $this->deadline->lt(today());
    }

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(KPI::class);
    }

    public function kpiAssignment(): BelongsTo
    {
        return $this->belongsTo(KPIAssignment::class, 'assignment_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by')->withTrashed();
    }

    public function hasLeaderScore(): bool
    {
        return $this->leader_score !== null;
    }
}

