<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('contract_types')->insert([
            [
                'contract_name' => 'Thử việc 2 tháng',
                'duration_month' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'contract_name' => 'Hợp đồng 6 tháng',
                'duration_month' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'contract_name' => 'Hợp đồng 1 năm',
                'duration_month' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'contract_name' => 'Hợp đồng 2 năm',
                'duration_month' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'contract_name' => 'Hợp đồng không xác định thời hạn',
                'duration_month' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'contract_name' => 'Hợp đồng thực tập',
                'duration_month' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}