<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'position_name',
        'base_salary',
        'description',
        'status',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
