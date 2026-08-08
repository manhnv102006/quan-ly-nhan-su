<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollComplaint extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_REJECTED = 'rejected';

    public const ISSUE_WRONG_SALARY = 'wrong_salary';

    public const ISSUE_WRONG_ATTENDANCE = 'wrong_attendance';

    public const ISSUE_WRONG_BONUS = 'wrong_bonus';

    public const ISSUE_WRONG_DEDUCTION = 'wrong_deduction';

    public const ISSUE_WRONG_ALLOWANCE = 'wrong_allowance';

    public const ISSUE_WRONG_OVERTIME = 'wrong_overtime';

    public const ISSUE_WRONG_TAX = 'wrong_tax';

    public const ISSUE_OTHER = 'other';

    protected $fillable = [
        'complaint_code',
        'employee_id',
        'payroll_id',
        'issue_type',
        'subject',
        'description',
        'disputed_amount',
        'status',
        'manager_note',
        'manager_confirmed_by',
        'manager_confirmed_at',
        'resolution_note',
        'resolved_by',
        'resolved_at',
        'rejected_by',
        'rejected_at',
        'reject_reason',
    ];

    protected function casts(): array
    {
        return [
            'disputed_amount' => 'decimal:0',
            'manager_confirmed_at' => 'datetime',
            'resolved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public static function issueTypeOptions(): array
    {
        return [
            self::ISSUE_WRONG_SALARY => 'Lương cơ bản / thực lĩnh sai',
            self::ISSUE_WRONG_ATTENDANCE => 'Chấm công / ngày công sai',
            self::ISSUE_WRONG_BONUS => 'Thưởng KPI sai',
            self::ISSUE_WRONG_DEDUCTION => 'Khấu trừ / phạt sai',
            self::ISSUE_WRONG_ALLOWANCE => 'Phụ cấp sai',
            self::ISSUE_WRONG_OVERTIME => 'Tăng ca sai',
            self::ISSUE_WRONG_TAX => 'Thuế / bảo hiểm sai',
            self::ISSUE_OTHER => 'Khác',
        ];
    }

    public function issueTypeLabel(): string
    {
        return self::issueTypeOptions()[$this->issue_type] ?? $this->issue_type;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function managerConfirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_confirmed_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PROCESSING => 'Chờ kế toán xử lý',
            self::STATUS_RESOLVED => 'Đã xử lý',
            self::STATUS_REJECTED => 'Từ chối',
            default => 'Chờ quản lý duyệt',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PROCESSING => 'bg-sky-50 text-sky-700 border-sky-100',
            self::STATUS_RESOLVED => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            self::STATUS_REJECTED => 'bg-rose-50 text-rose-700 border-rose-100',
            default => 'bg-amber-50 text-amber-700 border-amber-100',
        };
    }
}
