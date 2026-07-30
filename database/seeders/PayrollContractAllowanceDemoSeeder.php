<?php

namespace Database\Seeders;

use App\Models\AllowanceType;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Services\ContractAllowanceService;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Dữ liệu ảo tập trung test lương — phụ cấp — hợp đồng.
 *
 * Tạo phòng PAY-DEMO với 3 nhân viên, hợp đồng có đủ 5 loại phụ cấp (contract_allowances),
 * kỳ lương 12/2025 + chấm công + tính lương sẵn.
 *
 * Chạy: php artisan db:seed --class=PayrollContractAllowanceDemoSeeder
 */
class PayrollContractAllowanceDemoSeeder extends Seeder
{
    private const DEPT_CODE = 'PAY-DEMO';

    private const PERIOD_MONTH = 12;

    private const PERIOD_YEAR = 2025;

    /** @var array<string, int> */
    private array $allowanceTypeIds = [];

    public function run(): void
    {
        $this->command?->info('Đang tạo dữ liệu demo lương / phụ cấp / hợp đồng...');

        $this->call(AllowanceTypeSeeder::class);
        $this->allowanceTypeIds = AllowanceType::query()
            ->whereIn('code', [
                AllowanceType::CODE_MEAL,
                AllowanceType::CODE_PHONE,
                AllowanceType::CODE_FUEL,
                AllowanceType::CODE_POSITION,
                AllowanceType::CODE_FIXED,
            ])
            ->pluck('id', 'code')
            ->all();

        $contractTypeId = (int) (DB::table('contract_types')
            ->where('contract_name', 'like', '%1 năm%')
            ->value('id') ?? DB::table('contract_types')->value('id') ?? 3);

        $shiftId = (int) (DB::table('shifts')->value('id') ?? 1);
        $staffPositionId = (int) (DB::table('positions')->where('position_name', 'Nhân viên')->value('id') ?? 4);
        $managerPositionId = (int) (DB::table('positions')->where('position_name', 'Trưởng phòng')->value('id') ?? 2);
        $adminId = (int) (DB::table('users')->where('username', 'admin')->value('id') ?? 1);

        $department = Department::query()->updateOrCreate(
            ['department_code' => self::DEPT_CODE],
            [
                'department_name' => 'Phòng Demo Lương',
                'description' => 'Dữ liệu ảo test lương, phụ cấp, hợp đồng (seeder PAY-DEMO)',
                'max_employees' => 10,
                'status' => 'active',
            ]
        );

        $profiles = [
            [
                'code' => 'PAY001',
                'name' => 'Nguyễn Demo Lương A',
                'position_id' => $staffPositionId,
                'salary' => 15_000_000,
                'allowances' => [
                    AllowanceType::CODE_MEAL => 800_000,
                    AllowanceType::CODE_PHONE => 100_000,
                    AllowanceType::CODE_FUEL => 300_000,
                    AllowanceType::CODE_POSITION => 0,
                    AllowanceType::CODE_FIXED => 1_500_000,
                ],
                'attendance' => 'full',
            ],
            [
                'code' => 'PAY002',
                'name' => 'Trần Demo Lương B',
                'position_id' => $managerPositionId,
                'salary' => 25_000_000,
                'allowances' => [
                    AllowanceType::CODE_MEAL => 800_000,
                    AllowanceType::CODE_PHONE => 100_000,
                    AllowanceType::CODE_FUEL => 300_000,
                    AllowanceType::CODE_POSITION => 2_000_000,
                    AllowanceType::CODE_FIXED => 1_500_000,
                ],
                'attendance' => 'full_ot',
            ],
            [
                'code' => 'PAY003',
                'name' => 'Lê Demo Lương C',
                'position_id' => $staffPositionId,
                'salary' => 12_000_000,
                'allowances' => [
                    AllowanceType::CODE_MEAL => 660_000,
                    AllowanceType::CODE_PHONE => 50_000,
                    AllowanceType::CODE_FUEL => 100_000,
                    AllowanceType::CODE_POSITION => 0,
                    AllowanceType::CODE_FIXED => 1_000_000,
                ],
                'attendance' => 'partial',
            ],
        ];

        $employees = [];
        $allowanceService = app(ContractAllowanceService::class);

        foreach ($profiles as $profile) {
            $employee = Employee::query()->updateOrCreate(
                ['employee_code' => $profile['code']],
                [
                    'department_id' => $department->id,
                    'position_id' => $profile['position_id'],
                    'full_name' => $profile['name'],
                    'gender' => 'male',
                    'date_of_birth' => '1990-05-15',
                    'phone' => '0900000' . substr($profile['code'], -3),
                    'email' => strtolower($profile['code']) . '@demo.local',
                    'hire_date' => '2025-01-02',
                    'status' => 'active',
                ]
            );

            $allowanceInput = $this->mapAllowanceInput($profile['allowances']);
            $legacyColumns = $allowanceService->applyAllowanceInput($allowanceInput, $contractTypeId);

            $contract = Contract::query()->updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'contract_code' => 'HD_' . $profile['code'],
                ],
                array_merge($legacyColumns, [
                    'department_id' => $department->id,
                    'position_id' => $profile['position_id'],
                    'contract_type_id' => $contractTypeId,
                    'start_date' => '2025-01-01',
                    'end_date' => null,
                    'salary' => $profile['salary'],
                    'status' => Contract::STATUS_ACTIVE,
                    'signed_date' => '2025-01-01',
                    'description' => 'Hợp đồng demo test lương/phụ cấp',
                    'created_by' => $adminId,
                ])
            );

            $allowanceService->syncContractAllowances($contract->refresh(), $allowanceInput, $contractTypeId);

            $employees[] = [
                'employee' => $employee->fresh(),
                'contract' => $contract->fresh(),
                'profile' => $profile,
                'allowance_total' => $allowanceService->totalAllowance($contract),
            ];
        }

        $managerEmployee = collect($employees)->first(fn ($row) => $row['profile']['code'] === 'PAY002')['employee'];
        $department->update(['manager_id' => $managerEmployee->id]);

        $period = $this->upsertPeriod();
        $this->clearPeriodDataForDemoEmployees(collect($employees)->pluck('employee.id')->all(), $period);

        foreach ($employees as $row) {
            $this->seedAttendance(
                $row['employee']->id,
                $shiftId,
                $period,
                $row['profile']['attendance'],
            );

            if ($row['profile']['attendance'] === 'full_ot') {
                $this->seedOvertime($row['employee']->id, $period, $adminId, 4.0);
            }
        }

        Payroll::withTrashed()->where('payroll_period_id', $period->id)->forceDelete();
        app(PayrollService::class)->calculatePayrollForPeriod($period);
        $period->update(['status' => 'calculated']);

        $this->printSummary($department, $period, $employees);
    }

    /**
     * @param  array<string, float|int>  $amountsByCode
     * @return array<int, float>
     */
    private function mapAllowanceInput(array $amountsByCode): array
    {
        $input = [];
        foreach ($amountsByCode as $code => $amount) {
            $typeId = $this->allowanceTypeIds[$code] ?? null;
            if ($typeId) {
                $input[$typeId] = (float) $amount;
            }
        }

        return $input;
    }

    private function upsertPeriod(): PayrollPeriod
    {
        $start = Carbon::create(self::PERIOD_YEAR, self::PERIOD_MONTH, 1);
        $end = $start->copy()->endOfMonth();
        $label = str_pad((string) self::PERIOD_MONTH, 2, '0', STR_PAD_LEFT);

        return PayrollPeriod::query()->updateOrCreate(
            ['month' => self::PERIOD_MONTH, 'year' => self::PERIOD_YEAR],
            [
                'name' => "Kỳ lương DEMO {$label}/" . self::PERIOD_YEAR,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'status' => 'open',
                'deleted_at' => null,
            ]
        );
    }

    /**
     * @param  list<int>  $employeeIds
     */
    private function clearPeriodDataForDemoEmployees(array $employeeIds, PayrollPeriod $period): void
    {
        DB::table('attendances')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('attendance_date', [$period->start_date, $period->end_date])
            ->delete();

        DB::table('overtime_requests')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('work_date', [$period->start_date, $period->end_date])
            ->delete();
    }

    /**
     * @return list<string>
     */
    private function standardWorkingDays(PayrollPeriod $period): array
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

    private function seedAttendance(int $employeeId, int $shiftId, PayrollPeriod $period, string $scenario): void
    {
        $days = $this->standardWorkingDays($period);
        $total = count($days);

        if ($total === 0) {
            return;
        }

        $presentCount = match ($scenario) {
            'partial' => min(20, $total),
            default => $total,
        };

        for ($i = 0; $i < $presentCount; $i++) {
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
        }

        for ($i = $presentCount; $i < $total; $i++) {
            $date = $days[$i];
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
    }

    private function seedOvertime(int $employeeId, PayrollPeriod $period, int $adminId, float $hours): void
    {
        $workDate = Carbon::parse($period->start_date)->addDays(5)->format('Y-m-d');

        DB::table('overtime_requests')->updateOrInsert(
            [
                'employee_id' => $employeeId,
                'work_date' => $workDate,
            ],
            [
                'start_time' => '18:00',
                'end_time' => sprintf('%02d:00', 18 + (int) $hours),
                'total_hours' => $hours,
                'reason' => 'Tăng ca demo PAY-DEMO',
                'status' => 'approved',
                'approved_by' => $adminId,
                'approved_at' => "{$workDate} 17:30:00",
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * @param  list<array{employee: Employee, contract: Contract, profile: array<string, mixed>, allowance_total: float}>  $employees
     */
    private function printSummary(Department $department, PayrollPeriod $period, array $employees): void
    {
        $this->command?->newLine();
        $this->command?->info('Hoàn tất! Dữ liệu demo PAY-DEMO đã sẵn sàng.');
        $this->command?->line("Phòng ban: {$department->department_name} ({$department->department_code}, ID {$department->id})");
        $this->command?->line("Kỳ lương: {$period->name} (ID {$period->id}, trạng thái: calculated)");
        $this->command?->line("URL test: /admin/payroll-periods/{$period->id}/departments/{$department->id}");
        $this->command?->newLine();

        $rows = [];
        foreach ($employees as $row) {
            $payroll = Payroll::query()
                ->where('payroll_period_id', $period->id)
                ->where('employee_id', $row['employee']->id)
                ->first();

            $rows[] = [
                $row['profile']['code'],
                $row['profile']['name'],
                number_format((float) $row['contract']->salary, 0, ',', '.'),
                number_format($row['allowance_total'], 0, ',', '.'),
                $payroll ? number_format($payroll->totalAllowance(), 0, ',', '.') : '—',
                $row['profile']['attendance'],
            ];
        }

        $this->command?->table(
            ['Mã NV', 'Họ tên', 'Lương HĐ', 'Phụ cấp HĐ', 'Phụ cấp phiếu lương', 'Chấm công'],
            $rows
        );

        $this->command?->newLine();
        $this->command?->line('Gợi ý kiểm tra:');
        $this->command?->line('• PAY002 — phụ cấp HĐ = 4.700.000 (giống case Lê Văn Thành)');
        $this->command?->line('• PAY001 — phụ cấp HĐ = 2.700.000, đi làm đủ công');
        $this->command?->line('• PAY003 — phụ cấp HĐ = 1.810.000, thiếu công (20 ngày) nhưng phụ cấp vẫn khớp HĐ');
        $this->command?->line('• So sánh Hợp đồng → Chi tiết phụ cấp với modal phiếu lương');
    }
}
