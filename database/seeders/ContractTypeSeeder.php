<?php

namespace Database\Seeders;

use App\Models\ContractType;
use Illuminate\Database\Seeder;

class ContractTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'PROBATION_2M',
                'contract_name' => 'Thử việc 2 tháng',
                'category' => ContractType::CATEGORY_PROBATION,
                'duration_month' => 2,
                'description' => 'Hợp đồng thử việc thông thường 1-2 tháng',
            ],
            [
                'code' => 'FIXED_6M',
                'contract_name' => 'Hợp đồng xác định thời hạn 6 tháng',
                'category' => ContractType::CATEGORY_FIXED,
                'duration_month' => 6,
                'description' => 'Hợp đồng lao động xác định thời hạn ngắn',
            ],
            [
                'code' => 'FIXED_1Y',
                'contract_name' => 'Hợp đồng xác định thời hạn 1 năm',
                'category' => ContractType::CATEGORY_FIXED,
                'duration_month' => 12,
                'description' => 'Hợp đồng lao động 12 tháng',
            ],
            [
                'code' => 'FIXED_3Y',
                'contract_name' => 'Hợp đồng xác định thời hạn 3 năm',
                'category' => ContractType::CATEGORY_FIXED,
                'duration_month' => 36,
                'description' => 'Hợp đồng lao động 36 tháng',
            ],
            [
                'code' => 'INDEFINITE',
                'contract_name' => 'Hợp đồng không xác định thời hạn',
                'category' => ContractType::CATEGORY_INDEFINITE,
                'duration_month' => 0,
                'description' => 'Hợp đồng lao động không thời hạn',
            ],
            [
                'code' => 'SEASONAL',
                'contract_name' => 'Hợp đồng thời vụ / theo dự án',
                'category' => ContractType::CATEGORY_SEASONAL,
                'duration_month' => 3,
                'description' => 'Theo mùa vụ hoặc thời gian dự án',
            ],
            [
                'code' => 'COLLABORATOR',
                'contract_name' => 'Hợp đồng cộng tác viên',
                'category' => ContractType::CATEGORY_COLLABORATOR,
                'duration_month' => 6,
                'description' => 'Dành cho cộng tác viên, freelancer nội bộ',
            ],
            [
                'code' => 'INTERNSHIP',
                'contract_name' => 'Hợp đồng thực tập',
                'category' => ContractType::CATEGORY_INTERNSHIP,
                'duration_month' => 3,
                'description' => 'Sinh viên / thực tập sinh',
            ],
        ];

        foreach ($types as $type) {
            ContractType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
