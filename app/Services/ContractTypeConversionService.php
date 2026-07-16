<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractType;
use Illuminate\Validation\ValidationException;

class ContractTypeConversionService
{
    /**
     * @return array<int, string>
     */
    public function allowedTargetCategories(Contract $contract): array
    {
        $contract->loadMissing('contractType');
        $sourceCategory = $contract->contractType?->category;

        return match ($sourceCategory) {
            ContractType::CATEGORY_PROBATION => [
                ContractType::CATEGORY_FIXED,
                ContractType::CATEGORY_INDEFINITE,
            ],
            ContractType::CATEGORY_FIXED => array_values(array_filter([
                ContractType::CATEGORY_INDEFINITE,
                (int) $contract->renewal_count === 0 ? ContractType::CATEGORY_FIXED : null,
            ])),
            default => array_keys(ContractType::CATEGORY_LABELS),
        };
    }

    public function isTargetAllowed(Contract $contract, ContractType $targetType): bool
    {
        if ((int) $contract->contract_type_id === (int) $targetType->id) {
            return false;
        }

        return in_array($targetType->category, $this->allowedTargetCategories($contract), true);
    }

    public function assertConversionAllowed(Contract $contract, ContractType $targetType): void
    {
        $contract->loadMissing('contractType');

        if ((int) $contract->contract_type_id === (int) $targetType->id) {
            throw ValidationException::withMessages([
                'contract_type_id' => 'Loại hợp đồng đích phải khác loại hợp đồng hiện tại.',
            ]);
        }

        $sourceCategory = $contract->contractType?->category;
        $targetCategory = $targetType->category;

        if ($sourceCategory === ContractType::CATEGORY_PROBATION) {
            $this->assertProbationTarget($targetCategory);

            return;
        }

        if ($sourceCategory === ContractType::CATEGORY_FIXED) {
            $this->assertFixedTarget($targetCategory, (int) $contract->renewal_count);

            return;
        }
    }

    private function assertProbationTarget(string $targetCategory): void
    {
        if (! in_array($targetCategory, [ContractType::CATEGORY_FIXED, ContractType::CATEGORY_INDEFINITE], true)) {
            throw ValidationException::withMessages([
                'contract_type_id' => 'Từ Thử việc chỉ được chuyển sang Xác định thời hạn hoặc Không xác định thời hạn.',
            ]);
        }
    }

    private function assertFixedTarget(string $targetCategory, int $renewalCount): void
    {
        if ($targetCategory === ContractType::CATEGORY_INDEFINITE) {
            return;
        }

        if ($targetCategory === ContractType::CATEGORY_FIXED) {
            if ($renewalCount > 0) {
                throw ValidationException::withMessages([
                    'contract_type_id' => 'Hợp đồng xác định thời hạn đã gia hạn không thể chuyển sang loại xác định thời hạn khác. Vui lòng chuyển sang Không xác định thời hạn.',
                ]);
            }

            return;
        }

        throw ValidationException::withMessages([
            'contract_type_id' => 'Từ Xác định thời hạn chỉ được chuyển sang Không xác định thời hạn hoặc Xác định thời hạn (nếu chưa gia hạn).',
        ]);
    }
}
