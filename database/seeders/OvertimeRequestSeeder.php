<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OvertimeRequestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('overtime_requests')->insert([
            [
                'employee_id' => 1,
                'overtime_date' => '2026-06-15',
                'start_time' => '18:00:00',
                'end_time' => '21:00:00',
                'total_hours' => 3,
                'overtime_type' => 'weekday',
                'reason' => 'Hoàn thành dự án HRM',
                'status' => 'approved',
                'approved_by' => 2,
                'approved_at' => Carbon::now(),
                'manager_note' => 'Đã xác nhận',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'employee_id' => 1,
                'overtime_date' => '2026-06-18',
                'start_time' => '18:30:00',
                'end_time' => '22:00:00',
                'total_hours' => 3.5,
                'overtime_type' => 'weekday',
                'reason' => 'Fix bug hệ thống',
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'manager_note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'employee_id' => 2,
                'overtime_date' => '2026-06-20',
                'start_time' => '08:00:00',
                'end_time' => '12:00:00',
                'total_hours' => 4,
                'overtime_type' => 'weekend',
                'reason' => 'Hỗ trợ triển khai server',
                'status' => 'approved',
                'approved_by' => 1,
                'approved_at' => Carbon::now(),
                'manager_note' => 'Được duyệt',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'employee_id' => 3,
                'overtime_date' => '2026-06-22',
                'start_time' => '18:00:00',
                'end_time' => '20:00:00',
                'total_hours' => 2,
                'overtime_type' => 'weekday',
                'reason' => 'Hoàn thiện báo cáo KPI',
                'status' => 'rejected',
                'approved_by' => 1,
                'approved_at' => Carbon::now(),
                'manager_note' => 'Không cần tăng ca',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}