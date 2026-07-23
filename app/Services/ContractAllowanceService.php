<?php

namespace App\Services;

use App\Models\AllowanceType;
use App\Models\Contract;
use App\Models\ContractAllowance;
use App\Models\ContractType;
use Illuminate\Support\Collection;

class ContractAllowanceService
{
    /**
     * Ánh xạ các loại phụ cấp hệ thống sang cột legacy trên bảng contracts.
     * Chỉ dùng để giữ tương thích ngược cho hiển thị/báo cáo cũ.
     *
     * @var array<string, string>
     */
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
     * Giá trị điền sẵn cho form. Khi tạo mới (không có hợp đồng) trả về rỗng
     * để buộc người dùng phải tự nhập — không điền thì sẽ không có phụ cấp.
     *
     * @return array<int, float>
     */
    public function valuesForForm(?Contract $contract = null, ?int $positionId = null): array
    {
        if (! $contract) {
            return [];
        }

        $contract->loadMissing('contractAllowances');
        $saved = $contract->contractAllowances->keyBy('allowance_type_id');

        $values = [];
        foreach ($this->activeTypes() as $type) {
            if ($saved->has($type->id)) {
                $values[$type->id] = (float) $saved[$type->id]->amount;
            }
        }

        return $values;
    }

    /**
     * Suy ra các cột phụ cấp legacy từ dữ liệu người dùng nhập.
     * Không điền => 0 (không có phụ cấp). Không còn ép giá trị hardcode.
     *
     * @param  array<int|string, mixed>  $allowanceInput
     * @return array<string, float>
     */
    public function applyAllowanceInput(array $allowanceInput, ?int $contractTypeId, ?int $positionId = null): array
    {
        $types = $this->activeTypes()->keyBy('id');

        $columns = [
            'allowance' => 0.0,
            'allowance_meal' => 0.0,
            'allowance_phone' => 0.0,
            'allowance_fuel' => 0.0,
            'allowance_position' => 0.0,
        ];

        foreach ($allowanceInput as $typeId => $amount) {
            $type = $types->get((int) $typeId);
            if (! $type) {
                continue;
            }

            $column = self::COLUMN_MAP[$type->code] ?? null;
            if (! $column) {
                continue;
            }

            $columns[$column] = max(0, (float) $amount);
        }

        $contractType = $contractTypeId ? ContractType::find($contractTypeId) : null;
        if ($contractType?->isInternship()) {
            foreach ($columns as $key => $value) {
                $columns[$key] = 0.0;
            }
        }

        return $columns;
    }

    /**
     * Lưu phụ cấp theo hợp đồng dựa hoàn toàn trên dữ liệu người dùng nhập.
     * Khoản nào không nhập / bằng 0 sẽ bị bỏ (không có phụ cấp).
     *
     * @param  array<int|string, mixed>  $allowanceInput
     */
    public function syncContractAllowances(Contract $contract, array $allowanceInput, ?int $contractTypeId = null): void
    {
        $types = $this->activeTypes()->keyBy('id');
        $contractType = $contractTypeId
            ? ContractType::find($contractTypeId)
            : ContractType::find($contract->contract_type_id);
        $isInternship = $contractType?->isInternship() ?? false;

        $keepTypeIds = [];

        if (! $isInternship) {
            foreach ($allowanceInput as $typeId => $amount) {
                $type = $types->get((int) $typeId);
                if (! $type) {
                    continue;
                }

                $value = max(0, (float) $amount);
                if ($value <= 0) {
                    continue;
                }

                ContractAllowance::updateOrCreate(
                    [
                        'contract_id' => $contract->id,
                        'allowance_type_id' => $type->id,
                    ],
                    array_merge(
                        ['amount' => $value],
                        $this->metaFromType($type),
                    )
                );

                $keepTypeIds[] = $type->id;
            }
        }

        $contract->contractAllowances()
            ->when($keepTypeIds, fn ($q) => $q->whereNotIn('allowance_type_id', $keepTypeIds))
            ->delete();
    }

    /**
     * @return Collection<int, array{label: string, code: ?string, amount: float, note: ?string}>
     */
    public function breakdown(Contract $contract): Collection
    {
        $contract->loadMissing(['contractAllowances.allowanceType']);

        return $contract->contractAllowances
            ->filter(fn (ContractAllowance $item) => (float) $item->amount > 0)
            ->sortBy(fn (ContractAllowance $item) => $item->allowanceType?->sort_order ?? 99)
            ->map(fn (ContractAllowance $item) => [
                'allowance_type_id' => $item->allowance_type_id,
                'label' => $item->allowance_name ?? $item->allowanceType?->name ?? 'Phụ cấp',
                'code' => $item->allowance_code ?? $item->allowanceType?->code,
                'amount' => (float) $item->amount,
                'note' => $item->calculation_note ?? $item->allowanceType?->calculation_note,
                'calculation_type' => $item->calculation_type ?? $item->allowanceType?->calculation_type,
            ])
            ->values();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function snapshotLines(Contract $contract): array
    {
        return $this->breakdown($contract)
            ->map(fn (array $row) => [
                'allowance_type_id' => $row['allowance_type_id'] ?? null,
                'label' => $row['label'],
                'code' => $row['code'],
                'amount' => $row['amount'],
                'calculation_type' => $row['calculation_type'] ?? null,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $before
     * @param  list<array<string, mixed>>  $after
     * @return array{old: ?string, new: ?string}|null
     */
    public function diffAllowanceSnapshots(array $before, array $after): ?array
    {
        $format = fn (array $lines) => $this->formatAllowancesSummary($lines);

        $oldText = $format($before);
        $newText = $format($after);

        if ($oldText === $newText) {
            return null;
        }

        return [
            'old' => $oldText,
            'new' => $newText,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    public function formatAllowancesSummary(array $lines): ?string
    {
        if ($lines === []) {
            return null;
        }

        return collect($lines)
            ->map(function (array $line) {
                $label = $line['label'] ?? 'Phụ cấp';
                $amount = number_format((float) ($line['amount'] ?? 0), 0, ',', '.').'₫';

                return "{$label}: {$amount}";
            })
            ->implode(' · ');
    }

    /**
     * @return array<string, mixed>
     */
    protected function metaFromType(AllowanceType $type): array
    {
        return [
            'allowance_name' => $type->name,
            'allowance_code' => $type->code,
            'calculation_type' => $type->calculation_type,
            'calculation_note' => $type->calculation_note,
        ];
    }

    public function totalAllowance(Contract $contract): float
    {
        return (float) $this->breakdown($contract)->sum('amount');
    }
}
