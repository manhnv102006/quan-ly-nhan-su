<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeKPI extends Model
{
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
        'comment',
    ];

    protected $casts = [
        'deadline' => 'date',
        'progress' => 'integer',
    ];

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
}
