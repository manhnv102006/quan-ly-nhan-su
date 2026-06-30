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
}
