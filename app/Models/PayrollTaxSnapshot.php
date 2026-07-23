<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollTaxSnapshot extends Model
{
    protected $fillable = [
        'payroll_id',
        'tax_policy_id',
        'policy_code',
        'policy_label',
        'dependents_count',
        'personal_deduction',
        'dependent_deduction',
        'gross_income',
        'insurance_employee',
        'assessable_income',
        'taxable_income',
        'pit',
        'net_income',
        'brackets_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'dependents_count' => 'integer',
            'personal_deduction' => 'decimal:2',
            'dependent_deduction' => 'decimal:2',
            'gross_income' => 'decimal:2',
            'insurance_employee' => 'decimal:2',
            'assessable_income' => 'decimal:2',
            'taxable_income' => 'decimal:2',
            'pit' => 'decimal:2',
            'net_income' => 'decimal:2',
            'brackets_snapshot' => 'array',
        ];
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function taxPolicy(): BelongsTo
    {
        return $this->belongsTo(TaxPolicy::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toTaxRow(Employee $employee, Payroll $payroll): array
    {
        return [
            'gross' => (float) $this->gross_income,
            'insurance' => (float) $this->insurance_employee,
            'personal_deduction' => (float) $this->personal_deduction,
            'dependent_deduction' => (float) $this->dependent_deduction,
            'dependents_count' => (int) $this->dependents_count,
            'assessable_income' => (float) $this->assessable_income,
            'taxable_income' => (float) $this->taxable_income,
            'pit' => (float) $this->pit,
            'net_income' => (float) $this->net_income,
            'payroll' => $payroll,
            'employee' => $employee,
            'tax_policy' => $this->policy_label,
            'from_snapshot' => true,
        ];
    }
}
