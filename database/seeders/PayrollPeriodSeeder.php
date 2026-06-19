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
            $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
            $data[] = [
                'name' => "Kỳ lương tháng $monthStr/2026",
                'month' => $month,
                'year' => 2026,
                'start_date' => "2026-$monthStr-01",
                'end_date' => date('Y-m-t', strtotime("2026-$monthStr-01")),
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('payroll_periods')->insert($data);
    }
}