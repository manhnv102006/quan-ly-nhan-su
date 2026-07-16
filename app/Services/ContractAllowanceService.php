<?php

namespace App\Services;

use App\Models\AllowanceType;
use App\Models\Contract;
use App\Models\ContractAllowance;
use App\Models\ContractType;
use App\Models\Position;
use Illuminate\Support\Collection;

class ContractAllowanceService
{
    /** @var array<string, string> */
    public const COLUMN_MAP = [
        AllowanceType::CODE_MEAL => 'allowance_meal',
        AllowanceType::CODE_PHONE => 'allowance_phone',
        AllowanceType::CODE_FUEL => 'allowance_fuel',
        AllowanceType::CODE_POSITION => 'allowance_position',
        AllowanceType::CODE_FIXED => 'allowance',
    ];

    public function activeTypes(): Collection
    {
        return AllowanceType::query()->active()->ordered()->get();
    }

    /**
     * @return array<int, float>
     */
    public function valuesForForm(?Contract $contract = null, ?int $positionId = null): array
    {
        $types = $this->activeTypes();
        $values = [];

        if ($contract) {
            $contract->loadMissing('contractAllowances');
            $saved = $contract->contractAllowances->keyBy('allowance_type_id');

            foreach ($types as $type) {
                $values[$type->id] = (float) ($saved[$type->id]->amount ?? $this->defaultForType($type, $positionId));
            }

            return $values;
        }

        foreach ($types as $type) {
            $values[$type->id] = (float) $this->defaultForType($type, $positionId);
        }

        return $values;
    }

    public function defaultForType(AllowanceType $type, ?int $positionId = null): float
    {
        if ($type->code === AllowanceType::CODE_POSITION && $positionId) {
            $positionAllowance = Position::query()->whereKey($positionId)->value('allowance');

            return (float) ($positionAllowance ?: $type->default_amount);
        }

        return (float) $type->default_amount;
    }

    /**
     * @param  array<int|string, mixed>  $allowanceInput
     * @return array<string, mixed>
     */
    public function applyAllowanceInput(array $allowanceInput, ?int $contractTypeId, ?int $positionId = null): array
    {
        $types = $this->activeTypes()->keyBy('id');
        $columns = [];

        foreach ($allowanceInput as $typeId => $amount) {
            $type = $types->get((int) $typeId);
            if (!$type) {
                continue;
            }

            $column = self::COLUMN_MAP[$type->code] ?? null;
            if (!$column) {
                continue;
            }

            $columns[$column] = max(0, (float) $amount);
        }

        $contractType = $contractTypeId ? ContractType::find($contractTypeId) : null;
        $columns['allowance'] = $this->fixedAllowance($contractTypeId);

        if (isset($columns['allowance_position']) && $columns['allowance_position'] <= 0 && $positionId) {
            $columns['allowance_position'] = (float) (Position::query()->whereKey($positionId)->value('allowance') ?? 0);
        }

        if ($contractType?->isInternship()) {
            foreach (self::COLUMN_MAP as $column) {
                $columns[$column] = 0;
            }
        }

        return $columns;
    }

    /**
     * @param  array<int|string, mixed>  $allowanceInput
     */
    public function syncContractAllowances(Contract $contract, array $allowanceInput, ?int $contractTypeId = null): void
    {
        $types = $this->activeTypes()->keyBy('id');
        $contractType = $contractTypeId
            ? ContractType::find($contractTypeId)
            : ContractType::find($contract->contract_type_id);

        foreach ($types as $type) {
            $amount = isset($allowanceInput[$type->id])
                ? max(0, (float) $allowanceInput[$type->id])
                : $this->defaultForType($type, $contract->position_id);

            if ($type->code === AllowanceType::CODE_FIXED) {
                $amount = $this->fixedAllowance($contract->contract_type_id);
            }

            if ($contractType?->isInternship()) {
                $amount = 0;
            }

            ContractAllowance::updateOrCreate(
                [
                    'contract_id' => $contract->id,
                    'allowance_type_id' => $type->id,
                ],
                ['amount' => $amount]
            );
        }
    }

    public function breakdown(Contract $contract): Collection
    {
        $contract->loadMissing(['contractAllowances.allowanceType']);

        if ($contract->contractAllowances->isEmpty()) {
            return $this->activeTypes()->map(function (AllowanceType $type) use ($contract) {
                $column = self::COLUMN_MAP[$type->code] ?? null;
                $amount = $column ? (float) ($contract->{$column} ?? 0) : 0;

                return [
                    'label' => $type->name,
                    'code' => $type->code,
                    'amount' => $amount,
                    'note' => $type->calculation_note,
                ];
            });
        }

        return $contract->contractAllowances
            ->sortBy(fn(ContractAllowance $item) => $item->allowanceType?->sort_order ?? 99)
            ->map(fn(ContractAllowance $item) => [
                'label' => $item->allowanceType?->name ?? 'Phụ cấp',
                'code' => $item->allowanceType?->code,
                'amount' => (float) $item->amount,
                'note' => $item->allowanceType?->calculation_note,
            ])
            ->values();
    }

    public function totalAllowance(Contract $contract): float
    {
        return (float) $this->breakdown($contract)->sum('amount');
    }

    private function fixedAllowance(?int $contractTypeId): int
    {
        $type = $contractTypeId ? ContractType::find($contractTypeId) : null;

        return $type && $type->isInternship() ? 0 : ContractService::FIXED_ALLOWANCE;
    }
}
