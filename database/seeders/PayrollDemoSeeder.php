<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Dữ liệu mẫu để test quản lý lương (chấm công, hợp đồng, kỳ lương, tính lương).
 *
 * Chạy: php artisan db:seed --class=PayrollDemoSeeder
 */
class PayrollDemoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')->where('username', 'admin')->value('id') ?? 1;
        $shiftId = DB::table('shifts')->value('id') ?? 1;
        $contractTypeId = DB::table('contract_types')
            ->where('contract_name', 'like', '%1 năm%')
            ->value('id') ?? 3;

        $this->command?->info('Đang tạo dữ liệu mẫu quản lý lương...');

        $this->ensureActiveContracts($contractTypeId);

        $june = $this->upsertPeriod(6, 2026, 'open');
        $july = $this->upsertPeriod(7, 2026, 'open');
        $august = $this->upsertPeriod(8, 2026, 'open');
        $jan2027 = $this->upsertPeriod(1, 2027, 'open');

        $employees = Employee::query()->where('status', 'active')->orderBy('id')->get();

        if ($employees->isEmpty()) {
            $this->command?->warn('Không có nhân viên active. Chạy DatabaseSeeder trước.');

            return;
        }

        foreach ([$june, $july, $august, $jan2027] as $period) {
            $this->clearPeriodDemoData($period);
        }

        foreach ($employees as $index => $employee) {
            $scenario = $index % 5;

            $this->seedAttendanceScenario($employee, $june, $shiftId, $scenario);
            $this->seedAttendanceScenario($employee, $july, $shiftId, $scenario);
            $this->seedAttendanceScenario($employee, $august, $shiftId, $scenario);
            $this->seedAttendanceScenario($employee, $jan2027, $shiftId, $scenario);

            if (in_array($scenario, [0, 2], true)) {
                $this->seedOvertime($employee->id, $july, $adminId, $scenario === 0 ? 5.0 : 2.5);
                $this->seedOvertime($employee->id, $august, $adminId, 3.0);
            }
        }

        $payrollService = app(PayrollService::class);

        // Tháng 6: đã tính + duyệt + chi trả (demo luồng hoàn tất)
        $this->resetPayrolls($june);
        $payrollService->calculatePayrollForPeriod($june);
        Payroll::query()->where('payroll_period_id', $june->id)->update([
            'status' => 'paid',
            'approved_by' => $adminId,
            'approved_at' => Carbon::parse('2026-06-25 17:00:00'),
            'paid_by' => $adminId,
            'paid_at' => Carbon::parse('2026-06-28 09:00:00'),
        ]);
        $june->update([
            'status' => 'paid',
            'approved_by' => $adminId,
            'approved_at' => Carbon::parse('2026-06-25 17:00:00'),
            'paid_by' => $adminId,
            'paid_at' => Carbon::parse('2026-06-28 09:00:00'),
        ]);

        // Tháng 7: đã tính, chờ duyệt (demo bước duyệt/chi trả)
        $this->resetPayrolls($july);
        $payrollService->calculatePayrollForPeriod($july);
        $july->update(['status' => 'calculated']);

        // Tháng 8 & 01/2027: để OPEN — admin tự bấm "Tính lương" trên UI
        $this->resetPayrolls($august);
        $august->update(['status' => 'open']);

        $this->resetPayrolls($jan2027);
        $jan2027->update(['status' => 'open']);

        $this->command?->info('Hoàn tất! Dữ liệu mẫu lương đã sẵn sàng.');
        $this->command?->table(
            ['Kỳ lương', 'Trạng thái', 'Gợi ý test'],
            [
                ['06/2026', 'paid', 'Xem phiếu lương đã chi trả'],
                ['07/2026', 'calculated', 'Test Duyệt → Chi trả → Đóng kỳ'],
                ['08/2026', 'open', 'Test bấm "Tính lương" tự động'],
                ['01/2027', 'open', 'Test tính lương kỳ mới'],
            ]
        );
        $this->command?->table(
            ['Case NV', 'Mô tả'],
            [
                ['Case 1', 'Đi làm đủ công + tăng ca'],
                ['Case 2', 'Thiếu công (20/26), vắng không phép'],
                ['Case 3', 'Đủ công + đi muộn 3 lần + OT'],
                ['Case 4', '24 ngày đi làm + 2 ngày nghỉ phép có lương'],
                ['Case 5', '22 ngày đi làm + 4 ngày vắng không phép'],
            ]
        );
    }

    private function upsertPeriod(int $month, int $year, string $status): PayrollPeriod
    {
        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();
        $label = str_pad((string) $month, 2, '0', STR_PAD_LEFT);

        return PayrollPeriod::query()->updateOrCreate(
            ['month' => $month, 'year' => $year],
            [
                'name' => "Kỳ lương tháng {$label}/{$year}",
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'status' => $status,
                'deleted_at' => null,
            ]
        );
    }

    private function ensureActiveContracts(int $contractTypeId): void
    {
        $salaries = [
            'EMP001' => 50000000,
            'EMP002' => 25000000,
            'EMP003' => 18000000,
            'EMP004' => 12000000,
            'EMP005' => 15000000,
        ];

        Employee::query()->where('status', 'active')->each(function (Employee $employee) use ($contractTypeId, $salaries) {
            $hasActive = DB::table('contracts')
                ->where('employee_id', $employee->id)
                ->where('status', 'active')
                ->exists();

            if ($hasActive) {
                return;
            }

            $salary = $salaries[$employee->employee_code] ?? 15000000;

            DB::table('contracts')->insert([
                'employee_id' => $employee->id,
                'department_id' => $employee->department_id,
                'position_id' => $employee->position_id,
                'contract_type_id' => $contractTypeId,
                'contract_code' => 'HD_DEMO_' . $employee->employee_code,
                'start_date' => '2025-01-01',
                'end_date' => null,
                'salary' => $salary,
                'allowance' => 1500000,
                'allowance_meal' => 780000,
                'allowance_phone' => 50000,
                'allowance_fuel' => 100000,
                'allowance_position' => 500000,
                'status' => 'active',
                'signed_date' => '2025-01-01',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    private function clearPeriodDemoData(PayrollPeriod $period): void
    {
        $employeeIds = Employee::query()->where('status', 'active')->pluck('id');

        DB::table('attendances')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('attendance_date', [$period->start_date, $period->end_date])
            ->delete();

        DB::table('leave_requests')
            ->whereIn('employee_id', $employeeIds)
            ->where(function ($q) use ($period) {
                $q->whereBetween('start_date', [$period->start_date, $period->end_date])
                    ->orWhereBetween('end_date', [$period->start_date, $period->end_date]);
            })
            ->delete();

        DB::table('overtime_requests')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('work_date', [$period->start_date, $period->end_date])
            ->delete();
    }

    private function resetPayrolls(PayrollPeriod $period): void
    {
        Payroll::withTrashed()
            ->where('payroll_period_id', $period->id)
            ->forceDelete();
    }

    /**
     * @return list<string>
     */
    private function standardWorkingDaysList(PayrollPeriod $period): array
    {
        $days = [];
        $current = Carbon::parse($period->start_date)->copy();
        $end = Carbon::parse($period->end_date);

        while ($current->lte($end)) {
            if (! $current->isSunday()) {
                $days[] = $current->format('Y-m-d');
            }
            $current->addDay();
        }

        return $days;
    }

    private function seedAttendanceScenario(Employee $employee, PayrollPeriod $period, int $shiftId, int $scenario): void
    {
        $days = $this->standardWorkingDaysList($period);
        $total = count($days);

        if ($total === 0) {
            return;
        }

        match ($scenario) {
            0 => $this->insertPresentDays($employee->id, $shiftId, $days, 0, $total),
            1 => $this->insertPresentDays($employee->id, $shiftId, $days, 0, min(20, $total))
                + $this->insertAbsentDays($employee->id, $shiftId, array_slice($days, min(20, $total), min(6, $total - min(20, $total)))),
            2 => $this->insertMixedLatePresent($employee->id, $shiftId, $days),
            3 => $this->seedPaidLeaveScenario($employee->id, $shiftId, $days),
            default => $this->insertPresentDays($employee->id, $shiftId, $days, 0, min(22, $total))
                + $this->insertAbsentDays($employee->id, $shiftId, array_slice($days, min(22, $total), min(4, $total - min(22, $total)))),
        };
    }

    private function insertPresentDays(int $employeeId, int $shiftId, array $days, int $from, int $count): int
    {
        $inserted = 0;
        for ($i = $from; $i < min($from + $count, count($days)); $i++) {
            $date = $days[$i];
            DB::table('attendances')->insert([
                'employee_id' => $employeeId,
                'shift_id' => $shiftId,
                'attendance_date' => $date,
                'check_in' => "{$date} 08:00:00",
                'check_out' => "{$date} 17:00:00",
                'work_hours' => 8.00,
                'late_minutes' => 0,
                'status' => 'present',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $inserted++;
        }

        return $inserted;
    }

    private function insertAbsentDays(int $employeeId, int $shiftId, array $days): int
    {
        foreach ($days as $date) {
            DB::table('attendances')->insert([
                'employee_id' => $employeeId,
                'shift_id' => $shiftId,
                'attendance_date' => $date,
                'check_in' => null,
                'check_out' => null,
                'work_hours' => 0,
                'late_minutes' => 0,
                'status' => 'absent',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return count($days);
    }

    private function insertMixedLatePresent(int $employeeId, int $shiftId, array $days): void
    {
        foreach ($days as $idx => $date) {
            $isLate = $idx < 3;
            DB::table('attendances')->insert([
                'employee_id' => $employeeId,
                'shift_id' => $shiftId,
                'attendance_date' => $date,
                'check_in' => $isLate ? "{$date} 08:20:00" : "{$date} 08:00:00",
                'check_out' => "{$date} 17:00:00",
                'work_hours' => 8.00,
                'late_minutes' => $isLate ? 20 : 0,
                'status' => $isLate ? 'late' : 'present',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedPaidLeaveScenario(int $employeeId, int $shiftId, array $days): void
    {
        $presentCount = max(0, count($days) - 2);

        $this->insertPresentDays($employeeId, $shiftId, $days, 0, $presentCount);

        foreach (array_slice($days, $presentCount, 2) as $date) {
            DB::table('attendances')->insert([
                'employee_id' => $employeeId,
                'shift_id' => $shiftId,
                'attendance_date' => $date,
                'check_in' => null,
                'check_out' => null,
                'work_hours' => 0,
                'late_minutes' => 0,
                'status' => 'absent',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('leave_requests')->insert([
                'employee_id' => $employeeId,
                'leave_type' => 'annual',
                'start_date' => $date,
                'end_date' => $date,
                'reason' => 'Nghỉ phép năm (demo)',
                'total_days' => 1.0,
                'status' => 'approved',
                'approved_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedOvertime(int $employeeId, PayrollPeriod $period, int $adminId, float $hours): void
    {
        $workDate = Carbon::parse($period->start_date)->addDays(4)->format('Y-m-d');

        DB::table('overtime_requests')->insert([
            'employee_id' => $employeeId,
            'work_date' => $workDate,
            'start_time' => '18:00',
            'end_time' => sprintf('%02d:00', 18 + (int) $hours),
            'total_hours' => $hours,
            'reason' => 'Tăng ca demo tính lương',
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => "{$workDate} 17:30:00",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
