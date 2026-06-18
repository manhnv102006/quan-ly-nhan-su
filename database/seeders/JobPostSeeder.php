<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobPostSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('job_posts')->insert([
            [
                'department_id' => 2,
                'title' => 'Lập trình viên PHP',
                'quantity' => 3,
                'description' => 'Phát triển hệ thống quản lý nhân sự bằng Laravel',
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_id' => 1,
                'title' => 'Nhân viên HR tổng hợp',
                'quantity' => 2,
                'description' => 'Quản lý hồ sơ nhân sự và chấm công',
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_id' => 3,
                'title' => 'Kế toán viên',
                'quantity' => 1,
                'description' => 'Quản lý lương và báo cáo tài chính',
                'status' => 'closed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}