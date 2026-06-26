<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;

class PayrollPeriodSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('payroll_periods')->truncate();
        Schema::enableForeignKeyConstraints();

        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
            $startDate = "2026-$monthStr-01";
            $endDate = date('Y-m-t', strtotime($startDate));
            
            $status = 'open';
            $approvedBy = null;
            $approvedAt = null;
            $paidBy = null;
            $paidAt = null;

            if ($month <= 5) {
                $status = 'paid';
                $approvedBy = 1;
                $approvedAt = "2026-$monthStr-25 17:00:00";
                $paidBy = 1;
                $paidAt = "2026-$monthStr-28 09:00:00";
            } elseif ($month == 6) {
                $status = 'approved';
                $approvedBy = 1;
                $approvedAt = "2026-$monthStr-25 17:00:00";
            } elseif ($month == 7) {
                $status = 'calculated';
            }

            $data[] = [
                'name' => "Kỳ lương tháng $monthStr/2026",
                'month' => $month,
                'year' => 2026,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
                'paid_by' => $paidBy,
                'paid_at' => $paidAt,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('payroll_periods')->insert($data);
    }
}