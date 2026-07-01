<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KPI extends Model
{
    protected $table = 'kpis';

    public const PERIOD_MONTH = 'month';
    public const PERIOD_QUARTER = 'quarter';
    public const PERIOD_YEAR = 'year';

    public const POSITIONS = [
        'manager' => 'Manager',
    ];

    protected $fillable = [
        'code',
        'title',
        'description',
        'target',
        'unit',
        'weight',
        'max_score',
        'period',
        'start_date',
        'end_date',
        'positions',
        'department_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
        'positions' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Phòng ban áp dụng chính (giữ để tương thích ngược).
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Các phòng ban áp dụng KPI (nhiều - nhiều).
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'kpi_department', 'kpi_id', 'department_id');
    }

    /**
     * Get all assignments for this KPI.
     */
    public function assignments()
    {
        return $this->hasMany(KPIAssignment::class, 'kpi_id');
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'active' => 'Hoạt động',
            'inactive' => 'Ngừng áp dụng',
            default => 'N/A',
        };
    }

    /**
     * Nhãn kỳ đánh giá
     */
    public function getPeriodLabelAttribute(): string
    {
        return match ($this->period) {
            self::PERIOD_MONTH => 'Tháng',
            self::PERIOD_QUARTER => 'Quý',
            self::PERIOD_YEAR => 'Năm',
            default => 'N/A',
        };
    }

    /**
     * Danh sách nhãn chức vụ áp dụng
     */
    public function getPositionLabelsAttribute(): array
    {
        return collect($this->positions ?? [])
            ->map(fn ($position) => self::POSITIONS[$position] ?? $position)
            ->all();
    }
}
