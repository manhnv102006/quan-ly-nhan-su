<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('positions')->insert([
            [
                'position_name' => 'Giám đốc',
                'base_salary' => 50000000,
                'description' => 'Quản lý toàn bộ công ty',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'position_name' => 'Trưởng phòng',
                'base_salary' => 25000000,
                'description' => 'Quản lý phòng ban',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'position_name' => 'Phó phòng',
                'base_salary' => 18000000,
                'description' => 'Hỗ trợ trưởng phòng',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'position_name' => 'Nhân viên',
                'base_salary' => 10000000,
                'description' => 'Nhân viên chính thức',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'position_name' => 'Thực tập sinh',
                'base_salary' => 4000000,
                'description' => 'Sinh viên thực tập',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}