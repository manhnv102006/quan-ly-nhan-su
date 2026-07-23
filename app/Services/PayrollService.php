<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Holiday;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\AutoNotificationService;

class PayrollService
{
    public function __construct(
        private AutoNotificationService $notifications
    ) {}

    // Cấu hình số buổi nghỉ phép hưởng lương tối đa trong 1 tháng
    private const MAX_PAID_LEAVES_PER_MONTH = 1;


    private const STANDARD_MONTHLY_HOURS = 176;

    private const OVERTIME_RATE_MULTIPLIER = 1.5;

    /**
     * Tự động tính lương cho toàn bộ nhân viên hoạt động trong một kỳ lương.
     *
     * @param PayrollPeriod $period
     * @return string
     */
    public function calculatePayrollForPeriod(PayrollPeriod $period, ?int $departmentId = null): string
    {
        // 1. Kiểm tra xem kỳ lương này của phòng ban đã được tính trước đó chưa
        $existsQuery = Payroll::withTrashed()->where('payroll_period_id', $period->id);
        if ($departmentId) {
            $existsQuery->whereHas('employee', fn($q) => $q->where('department_id', $departmentId));
        }
        $exists = $existsQuery->exists();
        if ($exists) {
            return 'already_exists';
        }

        // 2. Lấy danh sách nhân viên đang hoạt động (lọc theo phòng ban nếu có)
        $employeesQuery = Employee::with(['position', 'contracts' => function ($query) {
            $query->where('status', 'active')->with(['contractType', 'contractAllowances.allowanceType']);
        }])->where('status', 'active');

        if ($departmentId) {
            $employeesQuery->where('department_id', $departmentId);
        }

        $employees = $employeesQuery->get();

        if ($employees->isEmpty()) {
            return 'no_employees';
        }

        $startDate = $period->start_date;
        $endDate = $period->end_date;

        // Tính ngày công chuẩn trong kỳ (Thứ 2 - Thứ 7, trừ Chủ nhật)
        $standardWorkingDays = $this->calculateStandardWorkingDays($startDate, $endDate);

        // Lấy danh sách ngày Lễ / Sự kiện trong kỳ
        $holidays = Holiday::inRange($startDate, $endDate)->get();
        $holidayDates = [];
        foreach ($holidays as $holiday) {
            $hStart = Carbon::parse($holiday->start_date)->max($startDate);
            $hEnd = Carbon::parse($holiday->end_date)->min($endDate);
            for ($date = $hStart->copy(); $date->lte($hEnd); $date->addDay()) {
                if (!$date->isSunday()) {
                    $holidayDates[] = $date->format('Y-m-d');
                }
            }
        }
        $holidayDates = array_unique($holidayDates);

        foreach ($employees as $employee) {
            // A. Lương hợp đồng (full tháng): Ưu tiên hợp đồng active còn hiệu lực trong kỳ, nếu không thì lấy từ chức vụ
            $activeContract = $employee->contracts
                ->filter(function ($contract) use ($startDate, $endDate) {
                    // Hợp đồng phải có khoảng hiệu lực giao với kỳ lương
                    $contractStart = $contract->start_date;
                    $contractEnd = $contract->end_date;

                    $startsBeforePeriodEnds = ! $contractStart || $contractStart <= $endDate;
                    $endsAfterPeriodStarts = ! $contractEnd || $contractEnd >= $startDate;

                    return $startsBeforePeriodEnds && $endsAfterPeriodStarts;
                })
                ->sortByDesc('start_date')
                ->first();
            $contractSalary = 0;

            if ($activeContract) {
                $contractSalary = $activeContract->salary;
            } elseif ($employee->position) {
                $contractSalary = $employee->position->base_salary;
            }

            // C. Chấm công: Đếm ngày đi làm thực tế (present + late)
            $presentDays = (float) $employee->attendances()
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->whereIn('status', ['present', 'late'])
                ->sum('work_ratio');

            $lateDays = $employee->attendances()
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->where('status', 'late')
                ->count();

            // D. Nghỉ phép: Tính số ngày nghỉ có lương và không lương
            $absentRecords = $employee->attendances()
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->where('status', 'absent')
                ->get();

            $approvedPaidLeavesCount = 0;
            $unapprovedAbsences = 0;

            // Lấy các ngày nghỉ lễ mà nhân viên KHÔNG ĐI LÀM (Nếu đi làm thì đã tính ở presentDays)
            $holidayPaidDays = 0;
            foreach ($holidayDates as $hDate) {
                $hasPresentRecord = $employee->attendances()
                    ->where('attendance_date', $hDate)
                    ->whereIn('status', ['present', 'late'])
                    ->exists();
                if (!$hasPresentRecord) {
                    $holidayPaidDays++;
                }
            }

            foreach ($absentRecords as $record) {
                // Bỏ qua nếu ngày vắng mặt trùng với ngày Lễ (Vì đã tính là holidayPaidDays)
                if (in_array($record->attendance_date->format('Y-m-d'), $holidayDates)) {
                    continue;
                }

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

            // E. Ngày công thực tế = ngày đi làm (present+late) + nghỉ phép hưởng lương + nghỉ lễ
            $actualWorkingDays = $presentDays + $paidLeaveDays + $holidayPaidDays;
            // Đảm bảo không vượt quá ngày công chuẩn
            $actualWorkingDays = min($actualWorkingDays, $standardWorkingDays);

            // B. Phụ cấp: tính động theo các khoản phụ cấp đã lưu trong hợp đồng.
            // Không đi làm ngày nào hoặc HĐ thực tập => không được hưởng phụ cấp.
            $isInternship = $activeContract?->contractType?->isInternship() ?? false;
            $noAllowance = $presentDays == 0 || $isInternship;

            $allowanceResult = $this->buildAllowanceSnapshot(
                $activeContract,
                $noAllowance,
                $presentDays,
                $actualWorkingDays,
                $standardWorkingDays
            );

            $allowanceSnapshots = $allowanceResult['snapshots'];
            $allowance = $allowanceResult['columns']['allowance'];
            $allowanceMeal = $allowanceResult['columns']['allowance_meal'];
            $allowancePhone = $allowanceResult['columns']['allowance_phone'];
            $allowanceFuel = $allowanceResult['columns']['allowance_fuel'];
            $allowancePosition = $allowanceResult['columns']['allowance_position'];
            $totalAllowance = $allowanceResult['total'];

            // F. Lương cơ bản PRO-RATA theo ngày công thực tế
            $basicSalary = $standardWorkingDays > 0
                ? round(($contractSalary / $standardWorkingDays) * $actualWorkingDays, 0)
                : $contractSalary;

            // G. Khấu trừ: Phạt đi trễ (50.000 VND / lần) + Phạt nghỉ không phép (300.000 VND / ngày)
            $deduction = ($lateDays * 50000) + ($unpaidLeaveDays * 300000);

            // H. Thưởng KPI: Tính điểm KPI trung bình và quy đổi thưởng
            $averageKpiScore = $employee->employeeKpis()
                ->whereHas('kpi')
                ->whereHas('kpiAssignment', function ($query) use ($startDate, $endDate) {
                    $query->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('start_date', [$startDate, $endDate])
                          ->orWhereBetween('end_date', [$startDate, $endDate])
                          ->orWhere(function ($q2) use ($startDate, $endDate) {
                              $q2->where('start_date', '<=', $startDate)
                                 ->where('end_date', '>=', $endDate);
                          });
                    });
                })
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

            // I. Lương tăng ca: tính theo từng loại ngày (từng hệ số)
            $overtimeRequests = OvertimeRequest::query()
                ->where('employee_id', $employee->id)
                ->whereIn('status', [OvertimeRequest::STATUS_APPROVED, OvertimeRequest::STATUS_COMPLETED])
                ->whereBetween('work_date', [$startDate, $endDate])
                ->get(['total_hours', 'rate_multiplier']);

            $standardMonthlyHours = $standardWorkingDays * 8;
            $hourlyRate = ($contractSalary > 0 && $standardMonthlyHours > 0)
                ? ($contractSalary / $standardMonthlyHours)
                : 0;
            
            $overtimeHours = 0;
            $overtimePay = 0;

            foreach ($overtimeRequests as $ot) {
                $hours = (float) $ot->total_hours;
                $rate = (float) $ot->rate_multiplier ?: self::OVERTIME_RATE_MULTIPLIER;
                $overtimeHours += $hours;
                $overtimePay += $hours * $hourlyRate * $rate;
            }
            $overtimePay = round($overtimePay, 0);

            // J. Thực lĩnh = Lương cơ bản (pro-rata) + Tổng phụ cấp + Thưởng KPI + Lương tăng ca - Khấu trừ
            $totalSalary = $basicSalary + $totalAllowance + $bonus + $overtimePay - $deduction;

            // Kiểm tra cảnh báo (nếu nhân viên nghỉ không phép dẫn đến không đủ 23 công HOẶC lương bị âm)
            if (($actualWorkingDays < 23 && $unpaidLeaveDays > 0) || $totalSalary < 0) {
                $this->notifications->employeeInsufficientWorkDaysWarning($employee, $period, $actualWorkingDays, $unpaidLeaveDays);
            }

            if ($totalSalary < 0) {
                $totalSalary = 0; // Không thể âm thực lĩnh
            }

            // K. Tạo bản ghi bảng lương
            $payroll = Payroll::create([
                'employee_id' => $employee->id,
                'payroll_period_id' => $period->id,
                'generated_by' => Auth::id() ?? 1, // Fallback cho seeder hoặc chạy CLI
                'basic_salary' => $basicSalary,
                'allowance' => $allowance,
                'allowance_meal' => $allowanceMeal,
                'allowance_phone' => $allowancePhone,
                'allowance_fuel' => $allowanceFuel,
                'allowance_position' => $allowancePosition,
                'bonus' => $bonus,
                'overtime_hours' => $overtimeHours,
                'overtime_pay' => $overtimePay,
                'standard_working_days' => $standardWorkingDays,
                'actual_working_days' => $actualWorkingDays,
                'deduction' => $deduction,
                'paid_leave_days' => $paidLeaveDays,
                'unpaid_leave_days' => $unpaidLeaveDays,
                'total_salary' => $totalSalary,
                'status' => 'calculated',
            ]);

            // Chốt (snapshot) từng khoản phụ cấp để không bị ảnh hưởng khi loại phụ cấp
            // hoặc hợp đồng thay đổi về sau.
            foreach ($allowanceSnapshots as $snapshot) {
                $payroll->payrollAllowances()->create($snapshot);
            }

        }

        // Cập nhật trạng thái kỳ lương sang calculated
        $period->update([
            'status' => 'calculated'
        ]);

        return 'success';
    }

    /**
     * Tính lại lương: Xóa các bản ghi lương hiện tại của kỳ và tính lại từ đầu.
     * Chỉ áp dụng khi kỳ lương đang ở trạng thái 'calculated'.
     *
     * @param PayrollPeriod $period
     * @return string
     */
    public function recalculatePayrollForPeriod(PayrollPeriod $period, ?int $departmentId = null): string
    {
        // Cho phép tính lại nếu ở trạng thái open hoặc calculated
        if (!in_array($period->status, ['open', 'calculated'])) {
            return 'invalid_status';
        }

        // Xóa vĩnh viễn tất cả các bản ghi lương của kỳ này (kể cả đã xóa mềm) để tránh trùng lặp unique constraint
        $deleteQuery = Payroll::withTrashed()->where('payroll_period_id', $period->id);
        if ($departmentId) {
            $deleteQuery->whereHas('employee', fn($q) => $q->where('department_id', $departmentId));
        }
        $deleteQuery->forceDelete();

        // Nếu không còn bất kỳ bảng lương nào trong kỳ này, đặt lại status về open
        $remainingPayrollsExists = Payroll::where('payroll_period_id', $period->id)->exists();
        if (!$remainingPayrollsExists) {
            $period->update(['status' => 'open']);
        }

        // Gọi lại hàm tính lương ban đầu
        return $this->calculatePayrollForPeriod($period, $departmentId);
    }

    /**
     * Tính các khoản phụ cấp cho kỳ lương dựa hoàn toàn trên phụ cấp đã lưu trong hợp đồng.
     * Trả về danh sách snapshot (để chốt lịch sử), các cột legacy (tương thích ngược) và tổng.
     *
     * @return array{snapshots: array<int, array<string, mixed>>, columns: array<string, float>, total: float}
     */
    private function buildAllowanceSnapshot(
        ?\App\Models\Contract $contract,
        bool $noAllowance,
        float $presentDays,
        float $actualWorkingDays,
        int $standardWorkingDays
    ): array {
        $columns = [
            'allowance' => 0.0,
            'allowance_meal' => 0.0,
            'allowance_phone' => 0.0,
            'allowance_fuel' => 0.0,
            'allowance_position' => 0.0,
        ];
        $snapshots = [];

        if (! $contract || $noAllowance) {
            return ['snapshots' => $snapshots, 'columns' => $columns, 'total' => 0.0];
        }

        $contract->loadMissing('contractAllowances.allowanceType');

        foreach ($contract->contractAllowances as $item) {
            $base = (float) $item->amount;
            if ($base <= 0) {
                continue;
            }

            $type = $item->allowanceType;
            $calcType = $type?->calculation_type ?? \App\Models\AllowanceType::CALC_PRORATA;

            $amount = match ($calcType) {
                \App\Models\AllowanceType::CALC_FIXED => $base,
                \App\Models\AllowanceType::CALC_PER_PRESENT_DAY => $standardWorkingDays > 0
                    ? round($base * ($presentDays / $standardWorkingDays), 0)
                    : 0.0,
                default => $standardWorkingDays > 0
                    ? round($base * ($actualWorkingDays / $standardWorkingDays), 0)
                    : $base,
            };

            if ($amount <= 0) {
                continue;
            }

            $code = $type?->code;
            $column = $code ? (ContractAllowanceService::COLUMN_MAP[$code] ?? 'allowance') : 'allowance';
            $columns[$column] += $amount;

            $snapshots[] = [
                'allowance_type_id' => $type?->id,
                'name' => $type?->name ?? 'Phụ cấp',
                'code' => $code,
                'amount' => $amount,
            ];
        }

        $total = (float) array_sum(array_column($snapshots, 'amount'));

        return ['snapshots' => $snapshots, 'columns' => $columns, 'total' => $total];
    }

    /**
     * Tính số ngày công chuẩn trong kỳ lương (Thứ 2 - Thứ 7, trừ Chủ nhật).
     */
    private function calculateStandardWorkingDays($startDate, $endDate): int
    {
        $days = 0;
        $current = \Carbon\Carbon::parse($startDate)->copy();
        $end = \Carbon\Carbon::parse($endDate);

        while ($current->lte($end)) {
            if (!$current->isSunday()) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }

    /**
     * Cập nhật tự động tiền tăng ca của nhân viên vào bảng lương nếu bảng lương đã tồn tại.
     */
    public function updatePayrollOvertime(int $employeeId, string $date): void
    {
        $carbonDate = \Carbon\Carbon::parse($date);

        // Tìm xem có bảng lương nào chứa ngày này không
        $payroll = Payroll::where('employee_id', $employeeId)
            ->whereHas('payrollPeriod', function($q) use ($carbonDate) {
                $q->whereDate('start_date', '<=', $carbonDate)
                  ->whereDate('end_date', '>=', $carbonDate);
            })
            ->first();

        if (!$payroll) {
            return;
        }

        $period = $payroll->payrollPeriod;
        $startDate = $period->start_date;
        $endDate = $period->end_date;

        // Tính lại tổng số giờ tăng ca của nhân viên đó trong kỳ (chỉ tính những đơn đã duyệt hoặc hoàn thành)
        $overtimeRequests = OvertimeRequest::query()
            ->where('employee_id', $employeeId)
            ->whereIn('status', [OvertimeRequest::STATUS_APPROVED, OvertimeRequest::STATUS_COMPLETED])
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get(['total_hours', 'rate_multiplier']);

        // Tìm lương hợp đồng / lương cơ bản gốc
        $employee = $payroll->employee;
        $activeContract = $employee?->contracts->first();
        $contractSalary = 0;
        if ($activeContract) {
            $contractSalary = $activeContract->salary;
        } elseif ($employee?->position) {
            $contractSalary = $employee->position->base_salary;
        }

        $standardWorkingDays = $payroll->standard_working_days;
        $standardMonthlyHours = $standardWorkingDays * 8;
        $hourlyRate = ($contractSalary > 0 && $standardMonthlyHours > 0)
            ? ($contractSalary / $standardMonthlyHours)
            : 0;

        $overtimeHours = 0;
        $overtimePay = 0;

        foreach ($overtimeRequests as $ot) {
            $hours = (float) $ot->total_hours;
            $rate = (float) $ot->rate_multiplier ?: self::OVERTIME_RATE_MULTIPLIER;
            $overtimeHours += $hours;
            $overtimePay += $hours * $hourlyRate * $rate;
        }
        $overtimePay = round($overtimePay, 0);

        // Tính lại tổng lương thực lĩnh (cộng đủ các khoản phụ cấp đã lưu)
        $totalAllowance = $payroll->totalAllowance();

        $totalSalary = (float) $payroll->basic_salary
            + $totalAllowance
            + (float) $payroll->bonus
            + $overtimePay
            - (float) $payroll->deduction;

        $payroll->update([
            'overtime_hours' => $overtimeHours,
            'overtime_pay' => $overtimePay,
            'total_salary' => max(0, $totalSalary)
        ]);
    }
}
