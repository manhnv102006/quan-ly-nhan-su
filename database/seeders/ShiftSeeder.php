<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('shifts')->insert([
            [
                'shift_name' => 'Ca hành chính',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shift_name' => 'Ca sáng',
                'start_time' => '08:00:00',
                'end_time' => '12:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shift_name' => 'Ca chiều',
                'start_time' => '13:00:00',
                'end_time' => '17:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shift_name' => 'Ca tối',
                'start_time' => '18:00:00',
                'end_time' => '22:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}