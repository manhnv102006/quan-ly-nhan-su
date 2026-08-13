<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

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
        'confirmed_adjustment_amount',
        'status',
        'manager_note',
        'manager_confirmed_by',
        'manager_confirmed_at',
        'resolution_note',
        'resolved_by',
        'resolved_at',
        'carried_to_payroll_id',
        'carried_at',
        'rejected_by',
        'rejected_at',
        'reject_reason',
    ];

    protected function casts(): array
    {
        return [
            'disputed_amount' => 'decimal:0',
            'confirmed_adjustment_amount' => 'decimal:0',
            'manager_confirmed_at' => 'datetime',
            'resolved_at' => 'datetime',
            'carried_at' => 'datetime',
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

    public function carriedToPayroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class, 'carried_to_payroll_id');
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

    public function isAwaitingAccountant(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING], true);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING], true);
    }

    public function isCarried(): bool
    {
        return $this->carried_to_payroll_id !== null;
    }

    public function awaitsCarryForward(): bool
    {
        return $this->status === self::STATUS_RESOLVED
            && $this->confirmed_adjustment_amount > 0
            && ! $this->isCarried();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PROCESSING, self::STATUS_PENDING => 'Chờ kế toán xử lý',
            self::STATUS_RESOLVED => 'Đã xử lý',
            self::STATUS_REJECTED => 'Từ chối',
            default => 'Chờ kế toán xử lý',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PROCESSING, self::STATUS_PENDING => 'bg-sky-50 text-sky-700 border-sky-100',
            self::STATUS_RESOLVED => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            self::STATUS_REJECTED => 'bg-rose-50 text-rose-700 border-rose-100',
            default => 'bg-sky-50 text-sky-700 border-sky-100',
        };
    }

    /**
     * @return Collection<int, array{at: ?\Illuminate\Support\Carbon, title: string, description: ?string, actor: ?string, tone: string, pending?: bool}>
     */
    public function historyTimeline(): Collection
    {
        $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');

        $events = collect();

        $events->push([
            'at' => $this->created_at,
            'title' => 'Gửi khiếu nại',
            'description' => 'Nhân viên gửi khiếu nại trực tiếp tới kế toán xử lý (không qua quản lý).',
            'actor' => $this->employee?->full_name,
            'tone' => 'sky',
        ]);

        if ($this->resolved_at) {
            $description = $this->resolution_note;

            if ($this->confirmed_adjustment_amount) {
                $adjustmentLine = 'Bổ sung chuyển tháng sau: '.$fmt($this->confirmed_adjustment_amount).' ₫';
                $description = $description
                    ? $adjustmentLine."\n".$description
                    : $adjustmentLine;
            }

            $events->push([
                'at' => $this->resolved_at,
                'title' => 'Kế toán xử lý — đồng ý',
                'description' => $description,
                'actor' => $this->resolver?->name ?? $this->resolver?->employee?->full_name,
                'tone' => 'emerald',
            ]);
        }

        if ($this->rejected_at) {
            $events->push([
                'at' => $this->rejected_at,
                'title' => 'Kế toán từ chối',
                'description' => $this->reject_reason,
                'actor' => $this->rejecter?->name ?? $this->rejecter?->employee?->full_name,
                'tone' => 'rose',
            ]);
        }

        if ($this->carried_at && $this->carriedToPayroll?->payrollPeriod) {
            $period = $this->carriedToPayroll->payrollPeriod;

            $events->push([
                'at' => $this->carried_at,
                'title' => 'Đã cộng vào bảng lương',
                'description' => sprintf(
                    'Kỳ %s (%s/%s)',
                    $period->name,
                    str_pad((string) $period->month, 2, '0', STR_PAD_LEFT),
                    $period->year,
                ),
                'actor' => null,
                'tone' => 'violet',
            ]);
        }

        if ($this->isAwaitingAccountant()) {
            $events->push([
                'at' => null,
                'title' => 'Chờ kế toán xử lý',
                'description' => 'Khiếu nại đang chờ kế toán xem xét.',
                'actor' => null,
                'tone' => 'amber',
                'pending' => true,
            ]);
        } elseif ($this->awaitsCarryForward()) {
            $events->push([
                'at' => null,
                'title' => 'Chờ cộng bổ sung tháng sau',
                'description' => 'Sẽ tự động cộng khi kế toán tính lương kỳ tiếp theo.',
                'actor' => null,
                'tone' => 'amber',
                'pending' => true,
            ]);
        }

        return $events
            ->sortBy(fn (array $event) => ($event['pending'] ?? false)
                ? PHP_INT_MAX
                : ($event['at']?->getTimestamp() ?? 0))
            ->values();
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [self::STATUS_RESOLVED, self::STATUS_REJECTED], true);
    }
}
