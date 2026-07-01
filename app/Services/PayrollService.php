<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\Auth;

class PayrollService
{
    // Cấu hình số buổi nghỉ phép hưởng lương tối đa trong 1 tháng
    private const MAX_PAID_LEAVES_PER_MONTH = 1;

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

            // B. Phụ cấp: Mặc định mỗi nhân viên được nhận 1,5 triệu VNĐ
            $allowance = 1500000;

            // C. Khấu trừ: Đi trễ (50.000 VND / lần) + Vắng mặt vượt phép (300.000 VND / ngày)
            $lateDays = $employee->attendances()
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->where('status', 'late')
                ->count();

            $absentRecords = $employee->attendances()
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->where('status', 'absent')
                ->get();

            $approvedPaidLeavesCount = 0;
            $unapprovedAbsences = 0;

            foreach ($absentRecords as $record) {
                // Kiểm tra xem ngày vắng mặt này có đơn nghỉ phép có lương (annual, sick) được duyệt không
                $hasLeave = $employee->leaveRequests()
                    ->where('status', 'approved')
                    ->whereIn('leave_type', ['annual', 'sick'])
                    ->whereDate('start_date', '<=', $record->attendance_date)
                    ->whereDate('end_date', '>=', $record->attendance_date)
                    ->exists();

                if ($hasLeave) {
                    $approvedPaidLeavesCount++;
                } else {
                    $unapprovedAbsences++;
                }
            }

            // Tính số ngày nghỉ có lương và không lương thực tế
            $paidLeaveDays = min($approvedPaidLeavesCount, self::MAX_PAID_LEAVES_PER_MONTH);
            $excessPaidLeaves = max(0, $approvedPaidLeavesCount - self::MAX_PAID_LEAVES_PER_MONTH);

            // Số ngày nghỉ bị trừ tiền = nghỉ không phép + nghỉ có phép vượt quá hạn mức
            $unpaidLeaveDays = $unapprovedAbsences + $excessPaidLeaves;

            $deduction = ($lateDays * 50000) + ($unpaidLeaveDays * 300000);

            // D. Thưởng KPI: Tính điểm KPI trung bình và quy đổi thưởng
            $averageKpiScore = $employee->employeeKpis()
                ->whereHas('kpi')
                ->avg('score');

            $bonus = 0;
            if ($averageKpiScore !== null) {
                // Nhận dạng thang điểm 10 hay 100
                $kpiPercentage = $averageKpiScore <= 10 ? $averageKpiScore * 10 : $averageKpiScore;

                if ($kpiPercentage < 70) {
                    $bonus = 0;
                } elseif ($kpiPercentage >= 70 && $kpiPercentage < 80) {
                    $bonus = 300000;
                } elseif ($kpiPercentage >= 80 && $kpiPercentage < 90) {
                    $bonus = 700000;
                } elseif ($kpiPercentage >= 90 && $kpiPercentage < 100) {
                    $bonus = 1200000;
                } else { // >= 100
                    $bonus = 2000000;
                }
            }



            // E. Thực lĩnh = Lương cơ bản + Phụ cấp + Thưởng KPI - Khấu trừ
            $totalSalary = $basicSalary + $allowance + $bonus - $deduction;
            if ($totalSalary < 0) {
                $totalSalary = 0; // Không thể âm thực lĩnh
            }

            // F. Tạo bản ghi bảng lương
            Payroll::create([
                'employee_id' => $employee->id,
                'payroll_period_id' => $period->id,
                'generated_by' => Auth::id() ?? 1, // Fallback cho seeder hoặc chạy CLI
                'basic_salary' => $basicSalary,
                'allowance' => $allowance,
                'bonus' => $bonus,
                'deduction' => $deduction,
                'paid_leave_days' => $paidLeaveDays,
                'unpaid_leave_days' => $unpaidLeaveDays,
                'total_salary' => $totalSalary,
            ]);
        }

        // Cập nhật trạng thái kỳ lương sang calculated
        $period->update([
            'status' => 'calculated'
        ]);

        return 'success';
    }
}
