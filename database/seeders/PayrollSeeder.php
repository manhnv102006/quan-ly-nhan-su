<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('payrolls')->insert([
            [
                'employee_id' => 1,
                'payroll_period_id' => 1,
                'generated_by' => 1,
                'basic_salary' => 50000000,
                'allowance' => 2000000,
                'bonus' => 1000000,
                'deduction' => 500000,
                'total_salary' => 52500000,
                'status' => 'paid',
                'approved_by' => 1,
                'approved_at' => now(),
                'paid_by' => 1,
                'paid_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 2,
                'payroll_period_id' => 1,
                'generated_by' => 1,
                'basic_salary' => 25000000,
                'allowance' => 1000000,
                'bonus' => 0,
                'deduction' => 200000,
                'total_salary' => 25800000,
                'status' => 'approved',
                'approved_by' => 1,
                'approved_at' => now(),
                'paid_by' => null,
                'paid_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 3,
                'payroll_period_id' => 1,
                'generated_by' => 1,
                'basic_salary' => 18000000,
                'allowance' => 500000,
                'bonus' => 500000,
                'deduction' => 0,
                'total_salary' => 19000000,
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'paid_by' => null,
                'paid_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 4,
                'payroll_period_id' => 1,
                'generated_by' => 1,
                'basic_salary' => 10000000,
                'allowance' => 1000000,
                'bonus' => 0,
                'deduction' => 0,
                'total_salary' => 11000000,
                'status' => 'draft',
                'approved_by' => null,
                'approved_at' => null,
                'paid_by' => null,
                'paid_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}