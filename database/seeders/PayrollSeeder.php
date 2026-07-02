<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing payrolls
        DB::table('payrolls')->truncate();

        $employees = DB::table('employees')->get();
        $periods = DB::table('payroll_periods')->get();

        foreach ($periods as $period) {
            // Chỉ tạo payrolls cho các kỳ lương có trạng thái calculated trở lên
            if ($period->status === 'open') {
                continue;
            }

            foreach ($employees as $employee) {
                // Determine salary based on employee code
                $basicSalary = 10000000;
                if ($employee->employee_code === 'EMP001') {
                    $basicSalary = 50000000;
                } elseif ($employee->employee_code === 'EMP002') {
                    $basicSalary = 25000000;
                } elseif ($employee->employee_code === 'EMP003') {
                    $basicSalary = 18000000;
                } elseif ($employee->employee_code === 'EMP004') {
                    $basicSalary = 12000000;
                } elseif ($employee->employee_code === 'EMP005') {
                    $basicSalary = 15000000;
                }

                // Random variations for allowance, bonus, deduction
                $allowance = rand(1, 4) * 500000; // 500k to 2M
                $bonus = rand(0, 3) * 500000; // 0 to 1.5M
                $deduction = rand(0, 2) * 200000; // 0 to 400k
                $totalSalary = $basicSalary + $allowance + $bonus - $deduction;

                // Map individual payroll status to the period's status
                $payrollStatus = 'calculated';
                if (in_array($period->status, ['approved', 'paid', 'closed'])) {
                    $payrollStatus = $period->status;
                }

                DB::table('payrolls')->insert([
                    'employee_id' => $employee->id,
                    'payroll_period_id' => $period->id,
                    'generated_by' => 1,
                    'basic_salary' => $basicSalary,
                    'allowance' => $allowance,
                    'bonus' => $bonus,
                    'deduction' => $deduction,
                    'total_salary' => $totalSalary,
                    'status' => $payrollStatus,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}