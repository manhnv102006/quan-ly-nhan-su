<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeTaxProfile extends Model
{
    /** Giá trị dự phòng khi chưa có bản ghi trong bảng tax_policies. */
    public const DEFAULT_PERSONAL_DEDUCTION = 15_500_000;

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
