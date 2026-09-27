<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tạo kỳ lương tháng 09/2026.
 * Phòng Công nghệ thông tin đi đủ công; các phòng khác có đủ ngày chấm công để tính lương.
 *
 * Chạy: php artisan db:seed --class=PayrollSep2026Seeder
 */
class PayrollSep2026Seeder extends Seeder
{
    private int $adminId;

    private int $defaultShiftId;

    public function run(): void
    {
        DB::disableQueryLog();

        $this->adminId = (int) (DB::table('users')->where('username', 'admin')->value('id') ?? 1);
        $this->defaultShiftId = (int) (DB::table('shifts')->where('shift_name', 'Ca hành chính')->value('id')
            ?? DB::table('shifts')->value('id')
            ?? 1);

        $employees = Employee::query()
            ->with('department:id,department_code,department_name')
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        if ($employees->isEmpty()) {
            $this->command?->warn('Không có nhân viên đang làm việc.');

            return;
        }

        $this->clearSeptember();

        $period = PayrollPeriod::query()->create([
            'name' => 'Kỳ lương tháng 09/2026',
            'month' => 9,
            'year' => 2026,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'status' => 'open',
            'is_active' => true,
        ]);

        $this->seedPeriodSources($period, $employees);

        $result = app(PayrollService::class)->calculatePayrollForPeriod($period);
        if ($result !== 'success') {
            $this->command?->error('Tính lương tháng 09/2026 thất bại: '.$result);

            return;
        }

        $period->refresh();
        $itPayrolls = Payroll::query()
            ->where('payroll_period_id', $period->id)
            ->whereHas('employee.department', fn ($q) => $q->where('department_code', 'IT'))
            ->with('employee:id,employee_code,full_name')
            ->orderBy('employee_id')
            ->get();

        $this->command?->info('Đã tạo kỳ 09/2026 — '.$period->status.', '.$period->payrolls()->count().' phiếu lương.');
        $this->command?->table(
            ['Mã', 'Nhân viên CNTT', 'Ngày công', 'Chuẩn', 'Tổng lương'],
            $itPayrolls->map(fn (Payroll $payroll) => [
                $payroll->employee->employee_code,
                $payroll->employee->full_name,
                $payroll->actual_working_days,
                $payroll->standard_working_days,
                number_format((float) $payroll->total_salary, 0, ',', '.'),
            ])->all()
        );
    }

    private function clearSeptember(): void
    {
        $periodIds = PayrollPeriod::withTrashed()->where('year', 2026)->where('month', 9)->pluck('id');

        if ($periodIds->isNotEmpty()) {
            $payrollIds = Payroll::withTrashed()->whereIn('payroll_period_id', $periodIds)->pluck('id');

            if ($payrollIds->isNotEmpty()) {
                foreach (['payroll_penalty_details', 'payroll_allowances', 'payroll_tax_snapshots'] as $table) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->whereIn('payroll_id', $payrollIds)->delete();
                    }
                }

                if (Schema::hasTable('payroll_complaints')) {
                    DB::table('payroll_complaints')->whereIn('payroll_id', $payrollIds)->delete();
                }
            }

            if (Schema::hasTable('salary_advance_deductions')) {
                DB::table('salary_advance_deductions')->whereIn('payroll_period_id', $periodIds)->delete();
            }

            Payroll::withTrashed()->whereIn('payroll_period_id', $periodIds)->forceDelete();
            PayrollPeriod::withTrashed()->whereIn('id', $periodIds)->forceDelete();
        }

        $from = '2026-09-01';
        $to = '2026-09-30';

        DB::table('attendances')->whereBetween('attendance_date', [$from, $to])->delete();
        DB::table('employee_shifts')->whereBetween('work_date', [$from, $to])->delete();
        DB::table('overtime_requests')->whereBetween('work_date', [$from, $to])->delete();
        DB::table('leave_requests')
            ->where('start_date', '<=', $to)
            ->where('end_date', '>=', $from)
            ->delete();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Employee>  $employees
     */
    private function seedPeriodSources(PayrollPeriod $period, $employees): void
    {
        $days = $this->standardWorkingDaysList($period);
        $total = count($days);
        $now = now();
        $attendances = [];
        $shifts = [];
        $leaves = [];
        $overtimes = [];

        foreach ($employees as $index => $employee) {
            $isIt = $employee->department?->department_code === 'IT';
            $scenario = $isIt ? 0 : $index % 5;

            foreach ($days as $idx => $date) {
                $shifts[] = [
                    'employee_id' => $employee->id,
                    'shift_id' => $this->defaultShiftId,
                    'work_date' => $date,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $attendances[] = $this->attendanceRow($employee->id, $date, $this->dayKind($scenario, $idx, $total), $now);
            }

            if ($scenario === 3) {
                foreach (array_slice($days, max(0, $total - 2)) as $date) {
                    $leaves[] = [
                        'employee_id' => $employee->id,
                        'leave_type' => 'annual',
                        'start_date' => $date,
                        'end_date' => $date,
                        'reason' => 'Nghỉ phép năm 09/2026',
                        'total_days' => 1,
                        'status' => 'approved',
                        'approved_by' => $this->adminId,
                        'approved_at' => $date.' 08:00:00',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (! $isIt && in_array($scenario, [0, 2], true)) {
                $workDate = '2026-09-04';
                $hours = $scenario === 0 ? 3.0 : 2.0;
                $overtimes[] = [
                    'employee_id' => $employee->id,
                    'work_date' => $workDate,
                    'start_time' => '18:00:00',
                    'end_time' => sprintf('%02d:00:00', 18 + (int) $hours),
                    'total_hours' => $hours,
                    'reason' => 'Tăng ca kỳ 09/2026',
                    'status' => 'approved',
                    'approved_by' => $this->adminId,
                    'approved_at' => $workDate.' 17:30:00',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($shifts, 500) as $chunk) {
            DB::table('employee_shifts')->insert($chunk);
        }
        foreach (array_chunk($attendances, 500) as $chunk) {
            DB::table('attendances')->insert($chunk);
        }
        if ($leaves !== []) {
            DB::table('leave_requests')->insert($leaves);
        }
        if ($overtimes !== []) {
            DB::table('overtime_requests')->insert($overtimes);
        }
    }

    private function dayKind(int $scenario, int $index, int $total): string
    {
        return match ($scenario) {
            1 => $index < min(20, $total) ? 'present' : 'absent',
            2 => $index < 3 ? 'late' : 'present',
            3 => $index < max(0, $total - 2) ? 'present' : 'absent',
            4 => $index < min(22, $total) ? 'present' : 'absent',
            default => 'present',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function attendanceRow(int $employeeId, string $date, string $kind, Carbon $now): array
    {
        $isAbsent = $kind === 'absent';
        $isLate = $kind === 'late';

        return [
            'employee_id' => $employeeId,
            'shift_id' => $this->defaultShiftId,
            'attendance_date' => $date,
            'check_in' => $isAbsent ? null : ($isLate ? "{$date} 08:20:00" : "{$date} 08:00:00"),
            'check_out' => $isAbsent ? null : "{$date} 17:00:00",
            'work_hours' => $isAbsent ? 0 : 8,
            'late_minutes' => $isLate ? 20 : 0,
            'work_ratio' => $isAbsent ? 0 : 1,
            'status' => $kind,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * @return list<string>
     */
    private function standardWorkingDaysList(PayrollPeriod $period): array
    {
        $days = [];
        $current = Carbon::parse($period->start_date)->startOfDay();
        $end = Carbon::parse($period->end_date)->startOfDay();

        while ($current->lte($end)) {
            if (! $current->isSunday()) {
                $days[] = $current->toDateString();
            }
            $current->addDay();
        }

        return $days;
    }
}
