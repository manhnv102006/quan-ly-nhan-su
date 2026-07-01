<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'payroll_period_id',
        'generated_by',
        'basic_salary',
        'allowance',
        'bonus',
        'deduction',
        'paid_leave_days',
        'unpaid_leave_days',
        'total_salary',
    ];

    protected $casts = [
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }
}
