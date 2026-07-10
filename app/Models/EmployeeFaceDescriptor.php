<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFaceDescriptor extends Model
{
    protected $fillable = [
        'employee_id',
        'embedding',
        'image_path',
        'quality',
        'model_name',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
            'quality' => 'float',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
