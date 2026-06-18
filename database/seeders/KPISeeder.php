<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KPISeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kpis')->insert([
            [
                'title' => 'Doanh số tháng',
                'description' => 'Đánh giá theo doanh thu đạt được trong tháng',
                'weight' => 50,
                'department_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Chấm công',
                'description' => 'Đi làm đúng giờ và đủ công',
                'weight' => 20,
                'department_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Chất lượng công việc',
                'description' => 'Hoàn thành nhiệm vụ đúng hạn và hiệu quả',
                'weight' => 30,
                'department_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}