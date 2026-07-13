<?php

namespace Database\Seeders;

use App\Models\AllowanceType;
use App\Models\Contract;
use App\Models\ContractAllowance;
use Illuminate\Database\Seeder;

class AllowanceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Phụ cấp ăn trưa',
                'code' => AllowanceType::CODE_MEAL,
                'default_amount' => 660_000,
                'calculation_note' => 'Chia theo ngày công thực tế trong tháng',
                'is_system' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Phụ cấp điện thoại',
                'code' => AllowanceType::CODE_PHONE,
                'default_amount' => 50_000,
                'calculation_note' => 'Trả cố định hàng tháng',
                'is_system' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Phụ cấp xăng xe',
                'code' => AllowanceType::CODE_FUEL,
                'default_amount' => 100_000,
                'calculation_note' => 'Trả cố định hàng tháng',
                'is_system' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Phụ cấp chức vụ',
                'code' => AllowanceType::CODE_POSITION,
                'default_amount' => 0,
                'calculation_note' => 'Mặc định theo chức vụ, có thể điều chỉnh',
                'is_system' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Phụ cấp hàng tháng (cố định)',
                'code' => AllowanceType::CODE_FIXED,
                'default_amount' => 1_500_000,
                'calculation_note' => 'Tự động theo loại HĐ (thực tập = 0)',
                'is_system' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($types as $type) {
            AllowanceType::updateOrCreate(['code' => $type['code']], $type);
        }

        $this->backfillContractAllowances();
    }

    private function backfillContractAllowances(): void
    {
        $typeMap = AllowanceType::query()->pluck('id', 'code');

        Contract::query()->chunkById(100, function ($contracts) use ($typeMap) {
            foreach ($contracts as $contract) {
                $rows = [
                    AllowanceType::CODE_MEAL => $contract->allowance_meal,
                    AllowanceType::CODE_PHONE => $contract->allowance_phone,
                    AllowanceType::CODE_FUEL => $contract->allowance_fuel,
                    AllowanceType::CODE_POSITION => $contract->allowance_position,
                    AllowanceType::CODE_FIXED => $contract->allowance,
                ];

                foreach ($rows as $code => $amount) {
                    $typeId = $typeMap[$code] ?? null;
                    if (! $typeId) {
                        continue;
                    }

                    ContractAllowance::updateOrCreate(
                        [
                            'contract_id' => $contract->id,
                            'allowance_type_id' => $typeId,
                        ],
                        ['amount' => $amount ?? 0]
                    );
                }
            }
        });
    }
}
