<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('attendances')->insert([
            [
                'employee_id' => 1,
                'shift_id' => 1,
                'attendance_date' => '2026-06-18',
                'check_in' => '2026-06-18 08:00:00',
                'check_out' => '2026-06-18 17:00:00',
                'work_hours' => 8.00,
                'status' => 'present',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 2,
                'shift_id' => 1,
                'attendance_date' => '2026-06-18',
                'check_in' => '2026-06-18 08:30:00',
                'check_out' => '2026-06-18 17:00:00',
                'work_hours' => 7.50,
                'status' => 'late',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 3,
                'shift_id' => 1,
                'attendance_date' => '2026-06-18',
                'check_in' => null,
                'check_out' => null,
                'work_hours' => 0,
                'status' => 'absent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}