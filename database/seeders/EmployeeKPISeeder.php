<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeKPISeeder extends Seeder
{
    public function run(): void
    {
        DB::table('employee_kpis')->insert([
            [
                'employee_id' => 1,
                'kpi_id' => 2,
                'assigned_by' => 1,
                'progress' => 80,
                'score' => 8.5,
                'comment' => 'Làm tốt nhưng cần cải thiện tốc độ',
                'status' => 'in_progress',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 2,
                'kpi_id' => 3,
                'assigned_by' => 1,
                'progress' => 100,
                'score' => 9.0,
                'comment' => 'Hoàn thành xuất sắc KPI',
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 4,
                'kpi_id' => 1,
                'assigned_by' => 2,
                'progress' => 30,
                'score' => null,
                'comment' => 'Mới bắt đầu thực hiện',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
