<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('candidates')->insert([
            [
                'job_post_id' => 1,
                'full_name' => 'Nguyễn Văn A',
                'phone' => '0901234567',
                'email' => 'a@gmail.com',
                'address' => 'Hà Nội',
                'birth_date' => '2003-05-10',
                'cv_file' => '/cv/a.pdf',
                'status' => 'new',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'job_post_id' => 1,
                'full_name' => 'Trần Thị B',
                'phone' => '0908765432',
                'email' => 'b@gmail.com',
                'address' => 'Hà Nội',
                'birth_date' => '2002-08-15',
                'cv_file' => '/cv/b.pdf',
                'status' => 'interview',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'job_post_id' => 2,
                'full_name' => 'Lê Văn C',
                'phone' => '0923456789',
                'email' => 'c@gmail.com',
                'address' => 'Hải Phòng',
                'birth_date' => '2001-12-20',
                'cv_file' => '/cv/c.pdf',
                'status' => 'passed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}