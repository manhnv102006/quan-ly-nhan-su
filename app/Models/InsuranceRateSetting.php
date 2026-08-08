<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceRateSetting extends Model
{
    protected $fillable = [
        'bhxh_employee_rate',
        'bhxh_employer_rate',
        'bhyt_employee_rate',
        'bhyt_employer_rate',
        'bhtn_employee_rate',
        'bhtn_employer_rate',
        'note',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'bhxh_employee_rate' => 'decimal:4',
            'bhxh_employer_rate' => 'decimal:4',
            'bhyt_employee_rate' => 'decimal:4',
            'bhyt_employer_rate' => 'decimal:4',
            'bhtn_employee_rate' => 'decimal:4',
            'bhtn_employer_rate' => 'decimal:4',
        ];
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        $setting = static::query()->first();

        if ($setting) {
            return $setting;
        }

        return static::query()->create(array_merge(
            static::systemDefaults(),
            ['note' => 'Mặc định theo quy định Việt Nam'],
        ));
    }

    /**
     * @return array<string, float>
     */
    public static function systemDefaults(): array
    {
        return [
            'bhxh_employee_rate' => 0.08,
            'bhxh_employer_rate' => 0.175,
            'bhyt_employee_rate' => 0.015,
            'bhyt_employer_rate' => 0.03,
            'bhtn_employee_rate' => 0.01,
            'bhtn_employer_rate' => 0.01,
        ];
    }

    /**
     * @return array<string, float>
     */
    public function toRatesArray(): array
    {
        return [
            'bhxh_employee_rate' => (float) $this->bhxh_employee_rate,
            'bhxh_employer_rate' => (float) $this->bhxh_employer_rate,
            'bhyt_employee_rate' => (float) $this->bhyt_employee_rate,
            'bhyt_employer_rate' => (float) $this->bhyt_employer_rate,
            'bhtn_employee_rate' => (float) $this->bhtn_employee_rate,
            'bhtn_employer_rate' => (float) $this->bhtn_employer_rate,
        ];
    }

    public function bhtnRatePercent(): float
    {
        return round((float) $this->bhtn_employee_rate * 100, 2);
    }
}
