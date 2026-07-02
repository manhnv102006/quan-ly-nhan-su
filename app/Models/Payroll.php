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
        'overtime_hours',
        'overtime_pay',
        'deduction',
        'paid_leave_days',
        'unpaid_leave_days',
        'total_salary',
        'status',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function displayStatus(): string
    {
        return $this->status ?? $this->payrollPeriod?->status ?? 'open';
    }

    public function statusLabel(): string
    {
        return match ($this->displayStatus()) {
            'open' => 'Đang mở',
            'calculated' => 'Đã tính lương',
            'approved' => 'Đã duyệt',
            'paid' => 'Đã thanh toán',
            'closed' => 'Đã đóng',
            'draft' => 'Nháp',
            'pending' => 'Chờ duyệt',
            default => 'Chưa xác định',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->displayStatus()) {
            'paid', 'closed' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'approved' => 'bg-sky-50 text-sky-700 border-sky-100',
            'calculated', 'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
            default => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }
}
