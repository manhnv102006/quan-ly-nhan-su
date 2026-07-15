<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxDependent extends Model
{
    public const DEFAULT_MONTHLY_DEDUCTION = 4_400_000;

    public const RELATIONSHIP_LABELS = [
        'child' => 'Con',
        'spouse' => 'Vợ/Chồng',
        'parent' => 'Cha/Mẹ',
        'other' => 'Khác',
    ];

    protected $fillable = [
        'employee_id',
        'full_name',
        'relationship',
        'date_of_birth',
        'id_number',
        'monthly_deduction',
        'start_date',
        'end_date',
        'is_active',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'monthly_deduction' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function relationshipLabel(): string
    {
        return self::RELATIONSHIP_LABELS[$this->relationship] ?? $this->relationship;
    }

    public function isEffectiveOn(\Carbon\Carbon $date): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->start_date && $this->start_date->gt($date)) {
            return false;
        }

        if ($this->end_date && $this->end_date->lt($date)) {
            return false;
        }

        return true;
    }
}
