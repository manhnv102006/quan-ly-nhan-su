<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\SalaryAdvance;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Dữ liệu mẫu kỳ lương tháng 08, 09, 10/2026:
 * - Phân ca làm việc (employee_shifts)
 * - Chấm công (attendances)
 * - Nghỉ phép (leave_requests)
 * - Ứng lương (salary_advances)
 *
 * Chạy: php artisan db:seed --class=PayrollAugSepOctDemoSeeder
 */
class PayrollAugSepOctDemoSeeder extends Seeder
{
    private int $adminId;

    private int $accountantId;

    private int $defaultShiftId;

    public function run(): void
    {
        $this->adminId = (int) (DB::table('users')->where('username', 'admin')->value('id') ?? 1);
        $this->accountantId = (int) (DB::table('users')->where('username', 'accountant')->value('id') ?? $this->adminId);
        $this->defaultShiftId = (int) (DB::table('shifts')->where('shift_name', 'Ca hành chính')->value('id')
            ?? DB::table('shifts')->value('id')
            ?? 1);

        $employees = Employee::query()->where('status', 'active')->orderBy('id')->get();

        if ($employees->isEmpty()) {
            $this->command?->warn('Không có nhân viên active.');

            return;
        }

        $this->command?->info('Đang tạo dữ liệu mẫu kỳ lương 08/09/10-2026...');

        $august = $this->upsertPeriod(8, 2026, 'open');
        $september = $this->upsertPeriod(9, 2026, 'open');
        $october = $this->upsertPeriod(10, 2026, 'open');

        foreach ([$august, $september, $october] as $period) {
            $this->clearPeriodData($period, $employees->pluck('id')->all());
            $this->resetPayrolls($period);
        }

        $this->clearDemoAdvances();

        foreach ($employees as $index => $employee) {
            $scenario = $index % 5;

            $this->seedEmployeeShifts($employee->id, $august);
            $this->seedEmployeeShifts($employee->id, $september);
            $this->seedEmployeeShifts($employee->id, $october);

            $this->seedAttendanceScenario($employee->id, $august, $scenario);
            $this->seedAttendanceScenario($employee->id, $september, $scenario);
            $this->seedAttendanceScenario($employee->id, $october, $scenario);

            if (in_array($scenario, [0, 2], true)) {
                $this->seedOvertime($employee->id, $august, 3.0);
                $this->seedOvertime($employee->id, $september, 2.5);
            }
        }

        $this->seedSalaryAdvances($employees);

        $payrollService = app(PayrollService::class);

        $this->resetPayrolls($august);
        $resultAug = $payrollService->calculatePayrollForPeriod($august);
        $august->update(['status' => $resultAug === 'success' ? 'calculated' : 'open']);

        $this->resetPayrolls($september);
        $resultSep = $payrollService->calculatePayrollForPeriod($september);
        $september->update(['status' => $resultSep === 'success' ? 'calculated' : 'open']);

        // Tháng 10: có đủ dữ liệu, để open để kế toán tự bấm "Tính lương"
        $october->update(['status' => 'open']);

        $this->printSummary($august, $september, $october);
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
                'is_active' => true,
                'deleted_at' => null,
            ]
        );
    }

    /**
     * @param  list<int>  $employeeIds
     */
    private function clearPeriodData(PayrollPeriod $period, array $employeeIds): void
    {
        if ($employeeIds === []) {
            return;
        }

        DB::table('attendances')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('attendance_date', [$period->start_date, $period->end_date])
            ->delete();

        DB::table('employee_shifts')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('work_date', [$period->start_date, $period->end_date])
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

    private function clearDemoAdvances(): void
    {
        DB::table('salary_advances')
            ->where('advance_code', 'like', 'TU-DEMO-26%')
            ->delete();
    }

    private function resetPayrolls(PayrollPeriod $period): void
    {
        Payroll::withTrashed()
            ->where('payroll_period_id', $period->id)
            ->forceDelete();
    }

    private function seedEmployeeShifts(int $employeeId, PayrollPeriod $period): void
    {
        foreach ($this->standardWorkingDaysList($period) as $date) {
            DB::table('employee_shifts')->insert([
                'employee_id' => $employeeId,
                'shift_id' => $this->defaultShiftId,
                'work_date' => $date,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
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

    private function seedAttendanceScenario(int $employeeId, PayrollPeriod $period, int $scenario): void
    {
        $days = $this->standardWorkingDaysList($period);
        $total = count($days);

        if ($total === 0) {
            return;
        }

        match ($scenario) {
            0 => $this->insertPresentDays($employeeId, $days, 0, $total),
            1 => $this->insertPresentDays($employeeId, $days, 0, min(20, $total))
                + $this->insertAbsentDays($employeeId, array_slice($days, min(20, $total), min(6, $total - min(20, $total)))),
            2 => $this->insertMixedLatePresent($employeeId, $days),
            3 => $this->seedPaidLeaveScenario($employeeId, $days),
            default => $this->insertPresentDays($employeeId, $days, 0, min(22, $total))
                + $this->insertAbsentDays($employeeId, array_slice($days, min(22, $total), min(4, $total - min(22, $total)))),
        };
    }

    private function insertPresentDays(int $employeeId, array $days, int $from, int $count): int
    {
        $inserted = 0;

        for ($i = $from; $i < min($from + $count, count($days)); $i++) {
            $date = $days[$i];
            DB::table('attendances')->insert([
                'employee_id' => $employeeId,
                'shift_id' => $this->defaultShiftId,
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

    private function insertAbsentDays(int $employeeId, array $days): int
    {
        foreach ($days as $date) {
            DB::table('attendances')->insert([
                'employee_id' => $employeeId,
                'shift_id' => $this->defaultShiftId,
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

    private function insertMixedLatePresent(int $employeeId, array $days): void
    {
        foreach ($days as $idx => $date) {
            $isLate = $idx < 3;
            DB::table('attendances')->insert([
                'employee_id' => $employeeId,
                'shift_id' => $this->defaultShiftId,
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

    private function seedPaidLeaveScenario(int $employeeId, array $days): void
    {
        $presentCount = max(0, count($days) - 2);
        $this->insertPresentDays($employeeId, $days, 0, $presentCount);

        foreach (array_slice($days, $presentCount, 2) as $date) {
            DB::table('attendances')->insert([
                'employee_id' => $employeeId,
                'shift_id' => $this->defaultShiftId,
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
                'reason' => 'Nghỉ phép năm (demo tháng '.Carbon::parse($date)->format('m/Y').')',
                'total_days' => 1.0,
                'status' => 'approved',
                'approved_by' => $this->adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedOvertime(int $employeeId, PayrollPeriod $period, float $hours): void
    {
        $workDate = Carbon::parse($period->start_date)->addDays(4)->format('Y-m-d');

        DB::table('overtime_requests')->insert([
            'employee_id' => $employeeId,
            'work_date' => $workDate,
            'start_time' => '18:00',
            'end_time' => sprintf('%02d:00', min(23, 18 + (int) $hours)),
            'total_hours' => $hours,
            'reason' => 'Tăng ca demo kỳ '.$period->month.'/'.$period->year,
            'status' => 'approved',
            'approved_by' => $this->adminId,
            'approved_at' => "{$workDate} 17:30:00",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Employee>  $employees
     */
    private function seedSalaryAdvances($employees): void
    {
        $pick = fn (int $offset = 0) => $employees->values()->get($offset % max(1, $employees->count()));

        $rows = [
            // Tháng 8: đã duyệt, sẵn sàng trừ vào lương tháng 9
            [
                'code' => 'TU-DEMO-2608-0001',
                'employee' => $pick(1),
                'amount' => 5_000_000,
                'request_date' => '2026-08-10',
                'status' => SalaryAdvance::STATUS_APPROVED,
                'reason' => 'Ứng lương giữa tháng 8 (demo)',
            ],
            [
                'code' => 'TU-DEMO-2608-0002',
                'employee' => $pick(3),
                'amount' => 3_000_000,
                'request_date' => '2026-08-18',
                'status' => SalaryAdvance::STATUS_APPROVED,
                'reason' => 'Chi phí đi lại công tác tháng 8',
            ],
            // Tháng 9: 1 chờ duyệt + 1 đang trừ dần
            [
                'code' => 'TU-DEMO-2609-0001',
                'employee' => $pick(0),
                'amount' => 5_000_000,
                'request_date' => '2026-09-05',
                'status' => SalaryAdvance::STATUS_PENDING,
                'reason' => 'Ứng lương tháng 9 - chờ kế toán duyệt',
            ],
            [
                'code' => 'TU-DEMO-2609-0002',
                'employee' => $pick(4),
                'amount' => 6_000_000,
                'request_date' => '2026-09-12',
                'status' => SalaryAdvance::STATUS_PARTIAL,
                'amount_settled' => 2_000_000,
                'reason' => 'Ứng lương tháng 9 - đang trừ dần',
            ],
            // Tháng 10: chờ duyệt
            [
                'code' => 'TU-DEMO-2610-0001',
                'employee' => $pick(2),
                'amount' => 5_000_000,
                'request_date' => '2026-10-03',
                'status' => SalaryAdvance::STATUS_PENDING,
                'reason' => 'Ứng lương đầu tháng 10 (demo)',
            ],
        ];

        foreach ($rows as $row) {
            /** @var Employee|null $employee */
            $employee = $row['employee'];
            if (! $employee) {
                continue;
            }

            $status = $row['status'];

            DB::table('salary_advances')->insert([
                'advance_code' => $row['code'],
                'employee_id' => $employee->id,
                'amount' => $row['amount'],
                'amount_settled' => $row['amount_settled'] ?? 0,
                'request_date' => $row['request_date'],
                'reason' => $row['reason'],
                'status' => $status,
                'requested_by' => $employee->user_id ?? $this->adminId,
                'approved_by' => in_array($status, [SalaryAdvance::STATUS_APPROVED, SalaryAdvance::STATUS_PARTIAL], true)
                    ? $this->accountantId
                    : null,
                'approved_at' => in_array($status, [SalaryAdvance::STATUS_APPROVED, SalaryAdvance::STATUS_PARTIAL], true)
                    ? Carbon::parse($row['request_date'])->addDay()
                    : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function printSummary(PayrollPeriod $august, PayrollPeriod $september, PayrollPeriod $october): void
    {
        $stats = fn (PayrollPeriod $period) => [
            'payrolls' => $period->payrolls()->count(),
            'attendances' => DB::table('attendances')
                ->whereBetween('attendance_date', [$period->start_date, $period->end_date])
                ->count(),
            'shifts' => DB::table('employee_shifts')
                ->whereBetween('work_date', [$period->start_date, $period->end_date])
                ->count(),
            'leaves' => DB::table('leave_requests')
                ->whereBetween('start_date', [$period->start_date, $period->end_date])
                ->count(),
            'total' => (float) $period->payrolls()->sum('total_salary'),
        ];

        $aug = $stats($august);
        $sep = $stats($september);
        $oct = $stats($october);

        $this->command?->info('Hoàn tất dữ liệu mẫu 08/09/10-2026!');
        $this->command?->table(
            ['Kỳ lương', 'Trạng thái', 'Chấm công', 'Ca làm', 'Nghỉ phép', 'Phiếu lương', 'Tổng lương'],
            [
                ['08/2026', $august->status, $aug['attendances'], $aug['shifts'], $aug['leaves'], $aug['payrolls'], number_format($aug['total'], 0, ',', '.').'₫'],
                ['09/2026', $september->status, $sep['attendances'], $sep['shifts'], $sep['leaves'], $sep['payrolls'], number_format($sep['total'], 0, ',', '.').'₫'],
                ['10/2026', $october->status.' (chưa tính)', $oct['attendances'], $oct['shifts'], $oct['leaves'], $oct['payrolls'], '—'],
            ]
        );
        $this->command?->info('Ứng lương demo: TU-DEMO-2608-xxxx, TU-DEMO-2609-xxxx, TU-DEMO-2610-xxxx');
        $this->command?->info('Tháng 10 đang để OPEN — vào Kỳ lương → bấm "Tính lương" để test.');
    }
}
