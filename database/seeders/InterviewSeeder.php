<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterviewSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('interviews')->insert([
            [
                'candidate_id' => 1,
                'interviewer_id' => 1,
                'interview_date' => '2026-06-20 09:00:00',
                'result' => 'pending',
                'note' => 'Ứng viên tiềm năng, cần đánh giá thêm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'candidate_id' => 2,
                'interviewer_id' => 2,
                'interview_date' => '2026-06-18 14:00:00',
                'result' => 'passed',
                'note' => 'Kỹ năng tốt, phù hợp vị trí',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'candidate_id' => 3,
                'interviewer_id' => null,
                'interview_date' => '2026-06-19 10:00:00',
                'result' => 'failed',
                'note' => 'Chưa đạt yêu cầu cơ bản',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}