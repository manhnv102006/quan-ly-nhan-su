<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxPolicy extends Model
{
    protected $fillable = [
        'code',
        'name',
        'effective_from',
        'effective_to',
        'personal_deduction',
        'dependent_deduction_default',
        'brackets',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'personal_deduction' => 'decimal:2',
            'dependent_deduction_default' => 'decimal:2',
            'brackets' => 'array',
        ];
    }

    public function payrollTaxSnapshots(): HasMany
    {
        return $this->hasMany(PayrollTaxSnapshot::class);
    }

    public static function forDate(Carbon $date): ?self
    {
        $on = $date->toDateString();

        return static::query()
            ->where('effective_from', '<=', $on)
            ->where(function ($q) use ($on) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $on);
            })
            ->orderByDesc('effective_from')
            ->first();
    }

    public static function current(): ?self
    {
        return static::forDate(now());
    }

    /**
     * @return array<int, array{0: float, 1: float}>
     */
    public function progressiveBrackets(): array
    {
        $brackets = $this->brackets ?? [];

        return array_map(function (array $row) {
            $limit = (float) ($row['limit'] ?? $row[0] ?? 0);
            $rate = (float) ($row['rate'] ?? $row[1] ?? 0);
            if ($limit <= 0 && $rate > 0) {
                $limit = PHP_FLOAT_MAX;
            }

            return [$limit, $rate];
        }, $brackets);
    }

    /**
     * Dùng khi chưa seed chính sách (dev / môi trường mới).
     */
    public static function fallbackForDate(Carbon $date): self
    {
        $policy = static::forDate($date);
        if ($policy) {
            return $policy;
        }

        $instance = new self([
            'code' => 'pit_fallback',
            'name' => 'Mặc định hệ thống',
            'effective_from' => $date->copy()->startOfYear(),
            'personal_deduction' => EmployeeTaxProfile::DEFAULT_PERSONAL_DEDUCTION,
            'dependent_deduction_default' => TaxDependent::DEFAULT_MONTHLY_DEDUCTION,
            'brackets' => self::defaultBrackets2026(),
        ]);
        $instance->id = 0;

        return $instance;
    }

    /**
     * @return list<array{limit: float, rate: float}>
     */
    public static function defaultBrackets2026(): array
    {
        return [
            ['limit' => 10_000_000, 'rate' => 0.05],
            ['limit' => 30_000_000, 'rate' => 0.10],
            ['limit' => 60_000_000, 'rate' => 0.20],
            ['limit' => 100_000_000, 'rate' => 0.30],
            ['limit' => PHP_FLOAT_MAX, 'rate' => 0.35],
        ];
    }

    /**
     * @return list<array{limit: float, rate: float}>
     */
    public static function defaultBracketsLegacy7(): array
    {
        return [
            ['limit' => 5_000_000, 'rate' => 0.05],
            ['limit' => 10_000_000, 'rate' => 0.10],
            ['limit' => 18_000_000, 'rate' => 0.15],
            ['limit' => 32_000_000, 'rate' => 0.20],
            ['limit' => 52_000_000, 'rate' => 0.25],
            ['limit' => 80_000_000, 'rate' => 0.30],
            ['limit' => PHP_FLOAT_MAX, 'rate' => 0.35],
        ];
    }
}
