<?php

namespace App\Services;

use App\Models\ContractType;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ContractTypeValidationService
{
    public const PROBATION_MAX_DAYS = 60;

    public const FIXED_MAX_MONTHS = 36;

    public const SEASONAL_MAX_MONTHS = 12;

    /**
     * @return array{end_date: ?string}
     */
    public function validateAndNormalize(ContractType $type, string $startDate, ?string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->startOfDay() : null;

        return match ($type->category) {
            ContractType::CATEGORY_PROBATION => $this->validateProbation($start, $end),
            ContractType::CATEGORY_FIXED => $this->validateFixed($start, $end),
            ContractType::CATEGORY_INDEFINITE => $this->validateIndefinite($end),
            ContractType::CATEGORY_SEASONAL => $this->validateSeasonal($start, $end),
            default => $this->validateDefault($type, $start, $end),
        };
    }

    /**
     * @return array{end_date: ?string}
     */
    private function validateProbation(Carbon $start, ?Carbon $end): array
    {
        if (! $end) {
            throw ValidationException::withMessages([
                'end_date' => 'Hợp đồng thử việc bắt buộc có ngày kết thúc.',
            ]);
        }

        $this->assertEndAfterStart($start, $end);

        if ($start->diffInDays($end) > self::PROBATION_MAX_DAYS) {
            throw ValidationException::withMessages([
                'end_date' => 'Hợp đồng thử việc tối đa '.self::PROBATION_MAX_DAYS.' ngày.',
            ]);
        }

        return ['end_date' => $end->toDateString()];
    }

    /**
     * @return array{end_date: ?string}
     */
    private function validateFixed(Carbon $start, ?Carbon $end): array
    {
        if (! $end) {
            throw ValidationException::withMessages([
                'end_date' => 'Hợp đồng xác định thời hạn bắt buộc có ngày kết thúc.',
            ]);
        }

        $this->assertEndAfterStart($start, $end);

        if ($start->diffInMonths($end) > self::FIXED_MAX_MONTHS) {
            throw ValidationException::withMessages([
                'end_date' => 'Hợp đồng xác định thời hạn tối đa '.self::FIXED_MAX_MONTHS.' tháng.',
            ]);
        }

        return ['end_date' => $end->toDateString()];
    }

    /**
     * @return array{end_date: ?string}
     */
    private function validateIndefinite(?Carbon $end): array
    {
        if ($end) {
            throw ValidationException::withMessages([
                'end_date' => 'Hợp đồng không xác định thời hạn không được có ngày kết thúc.',
            ]);
        }

        return ['end_date' => null];
    }

    /**
     * @return array{end_date: ?string}
     */
    private function validateSeasonal(Carbon $start, ?Carbon $end): array
    {
        if (! $end) {
            throw ValidationException::withMessages([
                'end_date' => 'Hợp đồng thời vụ bắt buộc có ngày kết thúc.',
            ]);
        }

        $this->assertEndAfterStart($start, $end);

        if ($start->diffInMonths($end) >= self::SEASONAL_MAX_MONTHS) {
            throw ValidationException::withMessages([
                'end_date' => 'Hợp đồng thời vụ phải dưới '.self::SEASONAL_MAX_MONTHS.' tháng.',
            ]);
        }

        return ['end_date' => $end->toDateString()];
    }

    /**
     * @return array{end_date: ?string}
     */
    private function validateDefault(ContractType $type, Carbon $start, ?Carbon $end): array
    {
        if ($type->isIndefinite()) {
            return $this->validateIndefinite($end);
        }

        if ($type->requiresEndDate() && ! $end) {
            throw ValidationException::withMessages([
                'end_date' => 'Loại hợp đồng này yêu cầu ngày kết thúc.',
            ]);
        }

        if ($end) {
            $this->assertEndAfterStart($start, $end);
        }

        return ['end_date' => $end?->toDateString()];
    }

    private function assertEndAfterStart(Carbon $start, Carbon $end): void
    {
        if ($end->lte($start)) {
            throw ValidationException::withMessages([
                'end_date' => 'Ngày kết thúc phải sau ngày bắt đầu.',
            ]);
        }
    }
}
