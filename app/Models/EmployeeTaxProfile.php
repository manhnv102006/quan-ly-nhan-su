<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeTaxProfile extends Model
{
    public const DEFAULT_PERSONAL_DEDUCTION = 11_000_000;

    protected $fillable = [
        'employee_id',
        'tax_code',
        'personal_deduction',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'personal_deduction' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
