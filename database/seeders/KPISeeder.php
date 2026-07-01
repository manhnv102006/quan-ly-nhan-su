<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KPISeeder extends Seeder
{
    public function run(): void
    {
        $kpis = [
            [
                'title' => 'Doanh số tháng',
                'description' => 'Đánh giá theo doanh thu đạt được trong tháng',
                'target' => '300 triệu',
                'unit' => 'Doanh số',
                'weight' => 50,
                'max_score' => 100,
                'period' => 'month',
                'positions' => ['employee', 'leader'],
                'department_id' => 4,
                'status' => 'active',
            ],
            [
                'title' => 'Chấm công',
                'description' => 'Đi làm đúng giờ và đủ công',
                'target' => '100%',
                'unit' => '%',
                'weight' => 20,
                'max_score' => 100,
                'period' => 'month',
                'positions' => ['employee'],
                'department_id' => 1,
                'status' => 'active',
            ],
            [
                'title' => 'Chất lượng công việc',
                'description' => 'Hoàn thành nhiệm vụ đúng hạn và hiệu quả',
                'target' => '30 Task',
                'unit' => 'Task',
                'weight' => 30,
                'max_score' => 100,
                'period' => 'quarter',
                'positions' => ['employee', 'leader', 'manager'],
                'department_id' => 2,
                'status' => 'active',
            ],
        ];

        foreach ($kpis as $index => $kpi) {
            $departmentId = $kpi['department_id'];
            $kpi['code'] = 'KPI' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
            $kpi['positions'] = json_encode($kpi['positions']);
            $kpi['created_at'] = now();
            $kpi['updated_at'] = now();

            $kpiId = DB::table('kpis')->insertGetId($kpi);

            DB::table('kpi_department')->insert([
                'kpi_id' => $kpiId,
                'department_id' => $departmentId,
            ]);
        }
    }
}