<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    /**
     * Get the KPI for this assignment.
     */
    public function kpi()
    {
        return $this->belongsTo(KPI::class, 'kpi_id');
    }

    /**
     * Get the manager assigned.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the admin who assigned this KPI.
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
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
