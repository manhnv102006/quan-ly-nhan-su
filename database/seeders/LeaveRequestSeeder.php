<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('leave_requests')->insert([
            [
                'employee_id' => 1,
                'leave_type' => 'annual',
                'start_date' => '2026-06-20',
                'end_date' => '2026-06-22',
                'reason' => 'Đi du lịch cùng gia đình',
                'status' => 'pending',
                'approved_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 2,
                'leave_type' => 'sick',
                'start_date' => '2026-06-18',
                'end_date' => '2026-06-19',
                'reason' => 'Bị sốt cao',
                'status' => 'approved',
                'approved_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 3,
                'leave_type' => 'unpaid',
                'start_date' => '2026-06-25',
                'end_date' => '2026-06-30',
                'reason' => 'Việc cá nhân',
                'status' => 'rejected',
                'approved_by' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}