<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiTask extends Model
{
    protected $table = 'kpi_tasks';

    protected $fillable = [
        'kpi_id',
        'title',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(KPI::class, 'kpi_id');
    }
}
