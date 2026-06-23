<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KPI extends Model
{
    protected $table = 'kpis';

    protected $fillable = [
        'title',
        'description',
        'weight',
        'department_id',
    ];

    /**
     * Get the department that owns the KPI.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
