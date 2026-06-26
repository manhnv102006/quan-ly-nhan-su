<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KPI extends Model
{
    protected $table = 'kpis';

    protected $fillable = [
        'code',
        'title',
        'description',
        'weight',
        'department_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the department that owns the KPI.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
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
        return match($this->status) {
            'active' => 'Hoạt động',
            'inactive' => 'Tạm ngưng',
            default => 'N/A',
        };
    }
}

