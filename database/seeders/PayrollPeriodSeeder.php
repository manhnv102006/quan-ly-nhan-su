<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $data[] = [
                'month' => $month,
                'year' => 2026,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('payroll_periods')->insert($data);
    }
}