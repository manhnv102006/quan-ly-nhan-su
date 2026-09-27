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
 * Xóa mọi kỳ lương năm 2026 rồi tạo lại dữ liệu tháng 01–08/2026.
 *
 * Chạy: php artisan db:seed --class=PayrollJanAug2026Seeder
 */
class PayrollJanAug2026Seeder extends Seeder
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

        $employees = Employee::query()->where('status', 'active')->orderBy('id')->get();

        if ($employees->isEmpty()) {
            $this->command?->warn('Không có nhân viên đang làm việc. Dừng tạo dữ liệu lương.');

            return;
        }

        $this->command?->info('Đang xóa kỳ lương năm 2026 và dữ liệu chấm công liên quan...');
        $this->wipeYear2026();

        $payrollService = app(PayrollService::class);
        $summary = [];

        for ($month = 1; $month <= 8; $month++) {
            $targetStatus = match (true) {
                $month <= 6 => 'paid',
                $month === 7 => 'approved',
                default => 'calculated',
            };

            $period = $this->createPeriod($month, 2026);
            $this->seedPeriodSources($period, $employees);

            $result = $payrollService->calculatePayrollForPeriod($period);
            if ($result !== 'success') {
                $this->command?->error("Tháng {$month}/2026 tính lương thất bại: {$result}");
                $summary[] = [$this->label($month), $result, 0, 0, '0'];

                continue;
            }

            $this->applyLifecycle($period, $targetStatus);
            $period->refresh();

            $payrollCount = $period->payrolls()->count();
            $total = (float) $period->payrolls()->sum('total_salary');
            $summary[] = [
                $this->label($month),
                $period->status,
                $payrollCount,
                DB::table('attendances')->whereBetween('attendance_date', [$period->start_date, $period->end_date])->count(),
                number_format($total, 0, ',', '.'),
            ];

            $this->command?->info("Đã xong kỳ {$this->label($month)} — {$period->status}, {$payrollCount} phiếu lương.");
        }

        $this->command?->info('Hoàn tất dữ liệu kỳ lương 01–08/2026.');
        $this->command?->table(
            ['Kỳ lương', 'Trạng thái', 'Phiếu lương', 'Chấm công', 'Tổng lương'],
            $summary
        );
    }

    private function wipeYear2026(): void
    {
        $periodIds = PayrollPeriod::withTrashed()->where('year', 2026)->pluck('id');

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
                    if (Schema::hasColumn('payroll_complaints', 'carried_to_payroll_id')) {
                        DB::table('payroll_complaints')->whereIn('carried_to_payroll_id', $payrollIds)->update([
                            'carried_to_payroll_id' => null,
                            'carried_at' => null,
                        ]);
                    }
                }
            }

            if (Schema::hasTable('salary_advance_deductions')) {
                DB::table('salary_advance_deductions')->whereIn('payroll_period_id', $periodIds)->delete();
            }

            if (Schema::hasTable('payroll_period_bank_documents')) {
                DB::table('payroll_period_bank_documents')->whereIn('payroll_period_id', $periodIds)->delete();
            }

            Payroll::withTrashed()->whereIn('payroll_period_id', $periodIds)->forceDelete();
            PayrollPeriod::withTrashed()->whereIn('id', $periodIds)->forceDelete();
        }

        $from = '2026-01-01';
        $to = '2026-12-31';

        DB::table('attendances')->whereBetween('attendance_date', [$from, $to])->delete();
        DB::table('employee_shifts')->whereBetween('work_date', [$from, $to])->delete();
        DB::table('overtime_requests')->whereBetween('work_date', [$from, $to])->delete();
        DB::table('leave_requests')
            ->where('start_date', '<=', $to)
            ->where('end_date', '>=', $from)
            ->delete();

        if (Schema::hasTable('salary_advances') && Schema::hasTable('salary_advance_deductions')) {
            DB::statement('
                UPDATE salary_advances sa
                LEFT JOIN (
                    SELECT salary_advance_id, SUM(amount) AS settled
                    FROM salary_advance_deductions
                    GROUP BY salary_advance_id
                ) d ON d.salary_advance_id = sa.id
                SET sa.amount_settled = COALESCE(d.settled, 0)
            ');

            DB::table('salary_advances')
                ->where('amount_settled', 0)
                ->whereIn('status', ['partial', 'settled'])
                ->update(['status' => 'approved']);
        }
    }

    private function createPeriod(int $month, int $year): PayrollPeriod
    {
        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();

        return PayrollPeriod::query()->create([
            'name' => 'Kỳ lương tháng '.$this->label($month),
            'month' => $month,
            'year' => $year,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'status' => 'open',
            'is_active' => true,
        ]);
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
            $scenario = $index % 5;

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
                        'reason' => 'Nghỉ phép năm '.$this->label($period->month),
                        'total_days' => 1,
                        'status' => 'approved',
                        'approved_by' => $this->adminId,
                        'approved_at' => $date.' 08:00:00',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (in_array($scenario, [0, 2], true)) {
                $workDate = Carbon::parse($period->start_date)->addDays(4)->toDateString();
                $hours = $scenario === 0 ? 3.0 : 2.0;
                $overtimes[] = [
                    'employee_id' => $employee->id,
                    'work_date' => $workDate,
                    'start_time' => '18:00:00',
                    'end_time' => sprintf('%02d:00:00', 18 + (int) $hours),
                    'total_hours' => $hours,
                    'reason' => 'Tăng ca kỳ '.$this->label($period->month),
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

    private function applyLifecycle(PayrollPeriod $period, string $targetStatus): void
    {
        if ($targetStatus === 'calculated') {
            return;
        }

        $approvedAt = Carbon::create($period->year, $period->month, 1)->addMonth()->day(5)->setTime(17, 0);
        $paidAt = Carbon::create($period->year, $period->month, 1)->addMonth()->day(8)->setTime(9, 0);

        Payroll::query()->where('payroll_period_id', $period->id)->update([
            'status' => 'approved',
            'approved_by' => $this->adminId,
            'approved_at' => $approvedAt,
        ]);
        $period->update([
            'status' => 'approved',
            'approved_by' => $this->adminId,
            'approved_at' => $approvedAt,
        ]);

        if ($targetStatus !== 'paid') {
            return;
        }

        Payroll::query()->where('payroll_period_id', $period->id)->update([
            'status' => 'paid',
            'paid_by' => $this->adminId,
            'paid_at' => $paidAt,
        ]);
        $period->update([
            'status' => 'paid',
            'paid_by' => $this->adminId,
            'paid_at' => $paidAt,
        ]);
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

    private function label(int $month): string
    {
        return str_pad((string) $month, 2, '0', STR_PAD_LEFT).'/2026';
    }
}
