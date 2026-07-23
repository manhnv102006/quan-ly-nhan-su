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

    /**
     * Cách tính phụ cấp khi lên bảng lương.
     * - prorata: chia theo số ngày công thực tế / ngày công chuẩn.
     * - per_present_day: chia theo số ngày đi làm thực tế / ngày công chuẩn.
     * - fixed: giữ nguyên số tiền, không pro-rata theo ngày công.
     */
    public const CALC_PRORATA = 'prorata';
    public const CALC_PER_PRESENT_DAY = 'per_present_day';
    public const CALC_FIXED = 'fixed';

    public const CALC_LABELS = [
        self::CALC_PRORATA => 'Chia theo ngày công thực tế',
        self::CALC_PER_PRESENT_DAY => 'Chia theo ngày đi làm thực tế',
        self::CALC_FIXED => 'Cố định (không chia theo ngày công)',
    ];

    protected $fillable = [
        'name',
        'code',
        'default_amount',
        'calculation_type',
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
