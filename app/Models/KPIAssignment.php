<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KPIAssignment extends Model
{
    protected $table = 'kpi_assignments';

    protected $fillable = [
        'kpi_id',
        'manager_id',
        'target',
        'start_date',
        'end_date',
        'note',
        'status',
        'assigned_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'target' => 'decimal:2',
    ];

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(KPI::class, 'kpi_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id')->withTrashed();
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by')->withTrashed();
    }

    public function employeeKpis(): HasMany
    {
        return $this->hasMany(EmployeeKPI::class, 'assignment_id');
    }

    public function getManagerNameAttribute(): string
    {
        if (! $this->manager) {
            return '—';
        }

        if ($this->manager->trashed()) {
            return $this->manager->name.' (đã xóa)';
        }

        return $this->manager->name;
    }

    public function getKpiCodeAttribute(): string
    {
        return $this->kpi?->code ?? '—';
    }

    public function getKpiTitleAttribute(): string
    {
        return $this->kpi?->title ?? '—';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Chờ phê duyệt',
            'active' => 'Đang thực hiện',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Hủy',
            default => 'N/A',
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-700',
            'active' => 'bg-blue-100 text-blue-700',
            'completed' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function getStatusTailwindAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
            'active' => 'bg-sky-50 text-sky-700 border-sky-100',
            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'cancelled' => 'bg-rose-50 text-rose-700 border-rose-100',
            default => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }

    public function getTargetUnitAttribute(): string
    {
        return $this->kpi?->unit ?: '%';
    }

    public function getIsPercentTargetAttribute(): bool
    {
        return $this->target_unit === '%';
    }

    public function getFormattedTargetAttribute(): string
    {
        $value = $this->formatTargetValue((float) $this->target);

        return $this->is_percent_target
            ? $value.'%'
            : $value.' '.$this->target_unit;
    }

    public function getTargetShortAttribute(): string
    {
        return $this->formatTargetValue((float) $this->target);
    }

    public function getTargetProgressAttribute(): float
    {
        if (! $this->is_percent_target) {
            return 0;
        }

        return min(100, max(0, (float) $this->target));
    }

    private function formatTargetValue(float $value): string
    {
        if (floor($value) == $value) {
            return (string) (int) $value;
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
