<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeKPI extends Model
{
    protected $table = 'employee_kpis';

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(KPI::class);
    }
}
