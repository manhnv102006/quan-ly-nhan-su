<?php

namespace App\Models;

use App\Services\ContractTypeValidationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractType extends Model
{
    use SoftDeletes;

    public const CATEGORY_PROBATION = 'probation';
    public const CATEGORY_FIXED = 'fixed';
    public const CATEGORY_INDEFINITE = 'indefinite';
    public const CATEGORY_SEASONAL = 'seasonal';
    public const CATEGORY_COLLABORATOR = 'collaborator';
    public const CATEGORY_INTERNSHIP = 'internship';

    public const CATEGORY_LABELS = [
        self::CATEGORY_PROBATION => 'Thử việc',
        self::CATEGORY_FIXED => 'Xác định thời hạn',
        self::CATEGORY_INDEFINITE => 'Không xác định thời hạn',
        self::CATEGORY_SEASONAL => 'Thời vụ / theo dự án',
        self::CATEGORY_COLLABORATOR => 'Cộng tác viên',
        self::CATEGORY_INTERNSHIP => 'Thực tập',
    ];

    protected $fillable = [
        'code',
        'contract_name',
        'category',
        'duration_month',
        'description',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function isInternship(): bool
    {
        if ($this->category === self::CATEGORY_INTERNSHIP) {
            return true;
        }

        $name = mb_strtolower($this->contract_name ?? '');

        return str_contains($name, 'thực tập')
            || str_contains($name, 'thuc tap')
            || str_contains($name, 'intern');
    }

    public function isIndefinite(): bool
    {
        return $this->category === self::CATEGORY_INDEFINITE || (int) $this->duration_month === 0;
    }

    public function requiresEndDate(): bool
    {
        return ! $this->isIndefinite();
    }

    /**
     * Dữ liệu để form tự tính ngày kết thúc theo thời hạn mặc định của loại HĐ.
     *
     * @return array{months: int, indefinite: bool, max_days: ?int, max_end_months: ?int, max_end_exclusive: bool}
     */
    public function durationHints(): array
    {
        return [
            'months' => (int) $this->duration_month,
            'indefinite' => $this->isIndefinite(),
            'max_days' => $this->category === self::CATEGORY_PROBATION
                ? ContractTypeValidationService::PROBATION_MAX_DAYS
                : null,
            'max_end_months' => match ($this->category) {
                self::CATEGORY_FIXED => ContractTypeValidationService::FIXED_MAX_MONTHS,
                self::CATEGORY_SEASONAL => ContractTypeValidationService::SEASONAL_MAX_MONTHS,
                default => null,
            },
            'max_end_exclusive' => $this->category === self::CATEGORY_SEASONAL,
        ];
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORY_LABELS[$this->category] ?? $this->category;
    }
}
