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

                // Count standard working days (Mon-Sat)
                $standardDays = 0;
                $current = \Carbon\Carbon::parse($period->start_date)->copy();
                $end = \Carbon\Carbon::parse($period->end_date);
                while ($current->lte($end)) {
                    if (!$current->isSunday()) {
                        $standardDays++;
                    }
                    $current->addDay();
                }

                $actualDays = rand($standardDays - 2, $standardDays);
                $contractSalary = $basicSalary;
                $proRatedBasic = $standardDays > 0 ? round(($contractSalary / $standardDays) * $actualDays, 0) : $contractSalary;

                // Random variations for allowance, bonus, deduction
                $allowance = rand(1, 4) * 500000; // 500k to 2M
                $bonus = rand(0, 3) * 500000; // 0 to 1.5M
                $deduction = rand(0, 2) * 200000; // 0 to 400k
                $totalSalary = $proRatedBasic + $allowance + $bonus - $deduction;

                // Determine status for individual payroll based on period and department
                $payrollStatus = 'calculated';
                $approvedBy = null;
                $approvedAt = null;
                $paidBy = null;
                $paidAt = null;

                if ($period->status === 'closed') {
                    $payrollStatus = 'closed';
                    $approvedBy = 1;
                    $approvedAt = now();
                    $paidBy = 1;
                    $paidAt = now();
                } elseif ($period->status === 'paid') {
                    $payrollStatus = 'paid';
                    $approvedBy = 1;
                    $approvedAt = now();
                    $paidBy = 1;
                    $paidAt = now();
                } elseif ($period->status === 'approved') {
                    $payrollStatus = 'approved';
                    $approvedBy = 1;
                    $approvedAt = now();
                } elseif ($period->status === 'calculated') {
                    // For calculated period, seed mixed statuses to cover all cases
                    // Odd department IDs will be approved, even will be calculated, some will not be seeded (remain open)
                    $deptId = $employee->department_id;
                    if ($deptId == 1) {
                        $payrollStatus = 'paid';
                        $approvedBy = 1;
                        $approvedAt = now();
                        $paidBy = 1;
                        $paidAt = now();
                    } elseif ($deptId == 2) {
                        $payrollStatus = 'approved';
                        $approvedBy = 1;
                        $approvedAt = now();
                    } elseif ($deptId == 3) {
                        $payrollStatus = 'calculated';
                    } else {
                        // Skip seeding for other departments so they show as "Open"
                        continue;
                    }
                }

                DB::table('payrolls')->insert([
                    'employee_id' => $employee->id,
                    'payroll_period_id' => $period->id,
                    'generated_by' => 1,
                    'basic_salary' => $proRatedBasic,
                    'allowance' => $allowance,
                    'bonus' => $bonus,
                    'standard_working_days' => $standardDays,
                    'actual_working_days' => $actualDays,
                    'deduction' => $deduction,
                    'total_salary' => $totalSalary,
                    'status' => $payrollStatus,
                    'approved_by' => $approvedBy,
                    'approved_at' => $approvedAt,
                    'paid_by' => $paidBy,
                    'paid_at' => $paidAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}