<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeInsurance extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_STOPPED = 'stopped';

    public const STATUS_LABELS = [
        self::STATUS_ACTIVE => 'Đang đóng',
        self::STATUS_SUSPENDED => 'Tạm dừng',
        self::STATUS_STOPPED => 'Đã ngừng',
    ];

    /**
     * Giới hạn tỷ lệ đóng BH tối đa (%) — chặn nhập nhầm ảnh hưởng lương/BH.
     *
     * @return array<string, array{max: float, label: string}>
     */
    public static function rateLimitsPercent(): array
    {
        return [
            'bhxh_employee_rate' => ['max' => 20, 'label' => 'BHXH NLĐ'],
            'bhxh_employer_rate' => ['max' => 30, 'label' => 'BHXH DN'],
            'bhyt_employee_rate' => ['max' => 10, 'label' => 'BHYT NLĐ'],
            'bhyt_employer_rate' => ['max' => 15, 'label' => 'BHYT DN'],
            'bhtn_rate' => ['max' => 10, 'label' => 'BHTN'],
        ];
    }

    protected $fillable = [
        'employee_id',
        'social_insurance_number',
        'health_insurance_code',
        'contribution_salary',
        'bhxh_employee_rate',
        'bhxh_employer_rate',
        'bhyt_employee_rate',
        'bhyt_employer_rate',
        'bhtn_employee_rate',
        'bhtn_employer_rate',
        'start_date',
        'end_date',
        'status',
        'stop_reason',
        'managed_by',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'contribution_salary' => 'decimal:2',
            'bhxh_employee_rate' => 'decimal:4',
            'bhxh_employer_rate' => 'decimal:4',
            'bhyt_employee_rate' => 'decimal:4',
            'bhyt_employer_rate' => 'decimal:4',
            'bhtn_employee_rate' => 'decimal:4',
            'bhtn_employer_rate' => 'decimal:4',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'bg-emerald-100 text-emerald-800',
            self::STATUS_SUSPENDED => 'bg-amber-100 text-amber-800',
            self::STATUS_STOPPED => 'bg-slate-100 text-slate-600',
            default => 'bg-slate-100 text-slate-600',
        };
    }

    public function isContributing(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
