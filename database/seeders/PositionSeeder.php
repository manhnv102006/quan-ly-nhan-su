<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa trùng lặp nếu có trước khi chạy seed
        DB::statement('DELETE p1 FROM positions p1 INNER JOIN positions p2 WHERE p1.id > p2.id AND p1.position_name = p2.position_name');

        $positions = [
            [
                'position_name' => 'Giám đốc',
                'base_salary' => 50000000,
                'allowance' => 2000000,
                'description' => 'Quản lý toàn bộ công ty',
                'status' => 'active',
            ],
            [
                'position_name' => 'Trưởng phòng',
                'base_salary' => 25000000,
                'allowance' => 1000000,
                'description' => 'Quản lý phòng ban',
                'status' => 'active',
            ],
            [
                'position_name' => 'Phó phòng',
                'base_salary' => 18000000,
                'allowance' => 600000,
                'description' => 'Hỗ trợ trưởng phòng',
                'status' => 'active',
            ],
            [
                'position_name' => 'Nhân viên',
                'base_salary' => 10000000,
                'allowance' => 300000,
                'description' => 'Nhân viên chính thức',
                'status' => 'active',
            ],
            [
                'position_name' => 'Thực tập sinh',
                'base_salary' => 4000000,
                'allowance' => 100000,
                'description' => 'Sinh viên thực tập',
                'status' => 'active',
            ],
        ];

        foreach ($positions as $pos) {
            DB::table('positions')->updateOrInsert(
                ['position_name' => $pos['position_name']],
                array_merge($pos, [
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                    'updated_at' => now(),
                ])
            );
        }
    }
}