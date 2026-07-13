<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AllowanceType extends Model
{
    public const CODE_MEAL = 'meal';
    public const CODE_PHONE = 'phone';
    public const CODE_FUEL = 'fuel';
    public const CODE_POSITION = 'position';
    public const CODE_FIXED = 'fixed';

    protected $fillable = [
        'name',
        'code',
        'default_amount',
        'description',
        'calculation_note',
        'is_system',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function contractAllowances(): HasMany
    {
        return $this->hasMany(ContractAllowance::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
