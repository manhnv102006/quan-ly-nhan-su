<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('contracts')->insert([
            [
                'employee_id' => 1,
                'department_id' => 1,
                'position_id' => 1,
                'contract_type_id' => 5,
                'contract_code' => 'HD001',
                'start_date' => '2025-01-01',
                'end_date' => null,
                'salary' => 50000000,
                'status' => 'active',
                'file_path' => '/contracts/hd001.pdf',
                'signed_date' => '2025-01-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 2,
                'department_id' => 2,
                'position_id' => 2,
                'contract_type_id' => 3,
                'contract_code' => 'HD002',
                'start_date' => '2025-02-01',
                'end_date' => '2026-02-01',
                'salary' => 25000000,
                'status' => 'active',
                'file_path' => '/contracts/hd002.pdf',
                'signed_date' => '2025-02-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 3,
                'department_id' => 3,
                'position_id' => 3,
                'contract_type_id' => 2,
                'contract_code' => 'HD003',
                'start_date' => '2025-03-01',
                'end_date' => '2025-09-01',
                'salary' => 18000000,
                'status' => 'expired',
                'file_path' => '/contracts/hd003.pdf',
                'signed_date' => '2025-03-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
