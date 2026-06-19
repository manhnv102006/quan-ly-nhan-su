<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\Auth;

class PayrollService
{
    /**
     * Tự động tính lương cho toàn bộ nhân viên hoạt động trong một kỳ lương.
     *
     * @param PayrollPeriod $period
     * @return string
     */
    public function calculatePayrollForPeriod(PayrollPeriod $period): string
    {
        // 1. Kiểm tra xem kỳ lương này đã được tính trước đó chưa
        $exists = Payroll::where('payroll_period_id', $period->id)->exists();
        if ($exists) {
            return 'already_exists';
        }

        // 2. Lấy danh sách toàn bộ nhân viên đang hoạt động
        $employees = Employee::with(['position', 'contracts' => function ($query) {
            $query->where('status', 'active');
        }])->where('status', 'active')->get();

        if ($employees->isEmpty()) {
            return 'no_employees';
        }

        $startDate = $period->start_date;
        $endDate = $period->end_date;

        foreach ($employees as $employee) {
            // A. Lương cơ bản: Ưu tiên lấy từ hợp đồng active, nếu không thì lấy từ chức vụ
            $activeContract = $employee->contracts->first();
            $basicSalary = 0;

            if ($activeContract) {
                $basicSalary = $activeContract->salary;
            } elseif ($employee->position) {
                $basicSalary = $employee->position->base_salary;
            }

            // B. Phụ cấp: Tính theo số ngày đi làm (status = 'present') trong kỳ
            // Công thức: 100.000 VND / ngày đi làm
            $presentDays = $employee->attendances()
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->where('status', 'present')
                ->count();

            $allowance = $presentDays * 100000;

            // C. Khấu trừ: Đi trễ (50.000 VND / lần) + Vắng mặt (300.000 VND / ngày) trong kỳ
            $lateDays = $employee->attendances()
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->where('status', 'late')
                ->count();

            $absentDays = $employee->attendances()
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->where('status', 'absent')
                ->count();

            $deduction = ($lateDays * 50000) + ($absentDays * 300000);

            // D. Thưởng KPI: Tính điểm KPI trung bình trong kỳ của nhân viên
            // Công thức: Điểm KPI trung bình * 200.000 VND
            $averageKpiScore = $employee->employeeKpis()
                ->whereHas('kpi')
                ->avg('score');

            $bonus = 0;
            if ($averageKpiScore !== null) {
                $bonus = $averageKpiScore * 200000;
            }

            // E. Thực lĩnh = Lương cơ bản + Phụ cấp + Thưởng KPI - Khấu trừ
            $totalSalary = $basicSalary + $allowance + $bonus - $deduction;
            if ($totalSalary < 0) {
                $totalSalary = 0; // Không thể âm thực lĩnh
            }

            // F. Tạo bản ghi bảng lương ở trạng thái nháp (draft)
            Payroll::create([
                'employee_id' => $employee->id,
                'payroll_period_id' => $period->id,
                'generated_by' => Auth::id() ?? 1, // Fallback cho seeder hoặc chạy CLI
                'basic_salary' => $basicSalary,
                'allowance' => $allowance,
                'bonus' => $bonus,
                'deduction' => $deduction,
                'total_salary' => $totalSalary,
                'status' => 'draft',
            ]);
        }

        return 'success';
    }
}
