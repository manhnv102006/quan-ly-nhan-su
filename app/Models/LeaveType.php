<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    /** Có trừ phép năm hay không. */
    public const ANNUAL_DEDUCTION_YES = 'yes';

    public const ANNUAL_DEDUCTION_NO = 'no';

    public const ANNUAL_DEDUCTION_COMPENSATORY = 'compensatory';

    public const ANNUAL_DEDUCTION_APPROVER = 'approver';

    public const ANNUAL_DEDUCTION_LABELS = [
        self::ANNUAL_DEDUCTION_YES => 'Có — trừ vào số dư phép năm',
        self::ANNUAL_DEDUCTION_NO => 'Không trừ phép năm',
        self::ANNUAL_DEDUCTION_COMPENSATORY => 'Không — trừ vào số dư ngày bù',
        self::ANNUAL_DEDUCTION_APPROVER => 'Theo quyết định của người duyệt',
    ];

    /** Lương trong ngày nghỉ do ai chi trả. */
    public const PAYER_COMPANY = 'company';

    public const PAYER_INSURANCE = 'insurance';

    public const PAYER_NONE = 'none';

    public const PAYER_APPROVER = 'approver';

    public const PAYER_LABELS = [
        self::PAYER_COMPANY => 'Công ty trả',
        self::PAYER_INSURANCE => 'BHXH trả',
        self::PAYER_NONE => 'Không trả lương',
        self::PAYER_APPROVER => 'Do người duyệt chọn',
    ];

    /** Cách áp dụng hạn mức nghỉ. */
    public const QUOTA_ANNUAL_BALANCE = 'annual_balance';

    public const QUOTA_COMPENSATORY_BALANCE = 'compensatory_balance';

    public const QUOTA_DAYS_PER_YEAR = 'days_per_year';

    public const QUOTA_DAYS_PER_EVENT = 'days_per_event';

    public const QUOTA_TIMES_PER_PREGNANCY = 'times_per_pregnancy';

    public const QUOTA_MONTHS_PER_EVENT = 'months_per_event';

    public const QUOTA_AGREEMENT = 'agreement';

    public const QUOTA_NONE = 'none';

    public const QUOTA_LABELS = [
        self::QUOTA_ANNUAL_BALANCE => 'Theo số dư phép năm',
        self::QUOTA_COMPENSATORY_BALANCE => 'Theo số ngày bù tích lũy',
        self::QUOTA_DAYS_PER_YEAR => 'Số ngày/năm',
        self::QUOTA_DAYS_PER_EVENT => 'Số ngày/lần nghỉ',
        self::QUOTA_TIMES_PER_PREGNANCY => 'Số lần/thai kỳ',
        self::QUOTA_MONTHS_PER_EVENT => 'Số tháng/lần nghỉ',
        self::QUOTA_AGREEMENT => 'Theo thỏa thuận',
        self::QUOTA_NONE => 'Không giới hạn',
    ];

    /** Các kiểu hạn mức có thể chặn tự động khi nhân viên gửi đơn. */
    public const ENFORCEABLE_QUOTA_TYPES = [
        self::QUOTA_DAYS_PER_YEAR,
        self::QUOTA_DAYS_PER_EVENT,
    ];

    /** Kiểu hạn mức bắt buộc phải nhập con số. */
    public const NUMERIC_QUOTA_TYPES = [
        self::QUOTA_DAYS_PER_YEAR,
        self::QUOTA_DAYS_PER_EVENT,
        self::QUOTA_TIMES_PER_PREGNANCY,
        self::QUOTA_MONTHS_PER_EVENT,
    ];

    public const GENDER_LABELS = [
        'male' => 'Chỉ nhân viên nam',
        'female' => 'Chỉ nhân viên nữ',
    ];

    /**
     * Bảng màu huy hiệu. Viết đầy đủ chuỗi class để Tailwind quét được.
     */
    public const COLOR_CLASSES = [
        'sky' => 'bg-sky-50 text-sky-700 border-sky-100',
        'amber' => 'bg-amber-50 text-amber-700 border-amber-100',
        'pink' => 'bg-pink-50 text-pink-700 border-pink-100',
        'rose' => 'bg-rose-50 text-rose-700 border-rose-100',
        'orange' => 'bg-orange-50 text-orange-700 border-orange-100',
        'fuchsia' => 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-100',
        'violet' => 'bg-violet-50 text-violet-700 border-violet-100',
        'indigo' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
        'cyan' => 'bg-cyan-50 text-cyan-700 border-cyan-100',
        'teal' => 'bg-teal-50 text-teal-700 border-teal-100',
        'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'red' => 'bg-red-50 text-red-700 border-red-100',
        'stone' => 'bg-stone-100 text-stone-700 border-stone-200',
        'slate' => 'bg-slate-100 text-slate-700 border-slate-200',
    ];

    public const COLOR_LABELS = [
        'sky' => 'Xanh trời',
        'amber' => 'Vàng cam',
        'pink' => 'Hồng',
        'rose' => 'Hồng đậm',
        'orange' => 'Cam',
        'fuchsia' => 'Tím hồng',
        'violet' => 'Tím',
        'indigo' => 'Chàm',
        'cyan' => 'Xanh ngọc',
        'teal' => 'Xanh teal',
        'emerald' => 'Xanh lục',
        'red' => 'Đỏ',
        'stone' => 'Xám đá',
        'slate' => 'Xám chì',
    ];

    public const DEFAULT_BADGE_CLASS = 'bg-slate-100 text-slate-600 border-slate-200';

    /**
     * Không được tắt: hệ thống tự tách phần phép năm vượt số dư sang nghỉ không lương,
     * nên hai loại này luôn phải dùng được.
     */
    public const PROTECTED_ACTIVE_CODES = ['annual', 'unpaid'];

    protected $fillable = [
        'code',
        'name',
        'description',
        'annual_deduction',
        'salary_payer',
        'salary_percent',
        'quota_type',
        'quota_days',
        'quota_note',
        'enforce_quota',
        'requires_document',
        'document_hint',
        'gender_restriction',
        'counts_as_leave',
        'auto_generated',
        'color',
        'is_active',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'salary_percent' => 'float',
        'quota_days' => 'float',
        'enforce_quota' => 'boolean',
        'requires_document' => 'boolean',
        'counts_as_leave' => 'boolean',
        'auto_generated' => 'boolean',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        $flush = fn () => \App\Support\LeaveTypeRegistry::flush();

        static::saved($flush);
        static::deleted($flush);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'leave_type', 'code');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function deductsAnnualLeave(): bool
    {
        return $this->annual_deduction === self::ANNUAL_DEDUCTION_YES;
    }

    public function isCompanyPaid(): bool
    {
        return $this->salary_payer === self::PAYER_COMPANY;
    }

    /**
     * Có người chi trả (công ty hoặc BHXH) — khác với nghỉ không lương.
     * Không dùng cho hạn mức 1 ngày lương/tháng của công ty.
     */
    public function isPaidLeave(): bool
    {
        return in_array($this->salary_payer, [self::PAYER_COMPANY, self::PAYER_INSURANCE], true);
    }

    /**
     * Chỉ phép năm / nghỉ nửa ngày (và loại custom trừ phép năm) mới chiếm
     * quỹ 1 ngày hưởng lương/tháng. Nghỉ việc riêng hưởng lương theo luật
     * (kết hôn, hiếu), ngày lễ tự sinh, công tác và chế độ BHXH không bị kẹp hạn mức nội bộ này.
     */
    public function countsTowardMonthlyPaidQuota(): bool
    {
        if (! $this->isCompanyPaid() || ! $this->counts_as_leave || $this->auto_generated) {
            return false;
        }

        return $this->deductsAnnualLeave() || $this->code === 'half_day';
    }

    public function requiresDocument(): bool
    {
        return (bool) $this->requires_document;
    }

    public function availableForGender(?string $gender): bool
    {
        if ($this->gender_restriction === null) {
            return true;
        }

        return $this->gender_restriction === $gender;
    }

    public function annualDeductionLabel(): string
    {
        return self::ANNUAL_DEDUCTION_LABELS[$this->annual_deduction] ?? $this->annual_deduction;
    }

    public function salaryPayerLabel(): string
    {
        $label = self::PAYER_LABELS[$this->salary_payer] ?? $this->salary_payer;

        if ($this->salary_percent !== null && (float) $this->salary_percent < 100.0 && $this->isPaidLeave()) {
            return $label.' '.rtrim(rtrim(number_format((float) $this->salary_percent, 2, ',', '.'), '0'), ',').'%';
        }

        return $label;
    }

    public function quotaLabel(): string
    {
        if (filled($this->quota_note)) {
            return $this->quota_note;
        }

        $days = $this->quota_days;
        $formatted = $days === null
            ? null
            : rtrim(rtrim(number_format($days, 1, ',', '.'), '0'), ',');

        return match ($this->quota_type) {
            self::QUOTA_ANNUAL_BALANCE => 'Theo số dư phép năm',
            self::QUOTA_COMPENSATORY_BALANCE => 'Theo số ngày bù tích lũy',
            self::QUOTA_DAYS_PER_YEAR => $formatted ? $formatted.' ngày/năm' : 'Theo số ngày/năm',
            self::QUOTA_DAYS_PER_EVENT => $formatted ? $formatted.' ngày/lần' : 'Theo số ngày/lần',
            self::QUOTA_TIMES_PER_PREGNANCY => $formatted ? $formatted.' lần/thai kỳ' : 'Theo số lần/thai kỳ',
            self::QUOTA_MONTHS_PER_EVENT => $formatted ? $formatted.' tháng/lần' : 'Theo số tháng/lần',
            self::QUOTA_AGREEMENT => 'Theo thỏa thuận',
            default => 'Không giới hạn',
        };
    }

    public function badgeClass(): string
    {
        return self::COLOR_CLASSES[$this->color] ?? self::DEFAULT_BADGE_CLASS;
    }

    public function quotaIsEnforced(): bool
    {
        return $this->enforce_quota
            && $this->quota_days !== null
            && $this->quota_days > 0
            && in_array($this->quota_type, self::ENFORCEABLE_QUOTA_TYPES, true);
    }
}
