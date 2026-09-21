<?php

use App\Models\Employee;
use App\Models\LeaveAccrual;
use App\Models\LeaveAccrualRun;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\LeaveBalanceService;
use App\Services\LeaveMonthlyAccrualService;
use App\Support\LeaveAccrualRules;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);

    Config::set('leave.mid_month_cutoff_day', 15);
    Config::set('leave.mid_month_after_cutoff', LeaveAccrualRules::AFTER_CUTOFF_NEXT_MONTH);
    Config::set('leave.accrual_days_per_month', 1);
    Config::set('leave.unpaid_leave_accrual_block_days', 12);
    Config::set('leave.sick_leave_accrual_allowed_months', 2);
});

function createAccrualEmployee(string $hireDate, string $codeSuffix = ''): Employee
{
    $user = User::factory()->create([
        'role_id' => Role::where('name', Role::EMPLOYEE)->value('id'),
        'status' => 'active',
    ]);

    return Employee::create([
        'user_id' => $user->id,
        'employee_code' => 'NV-ACC-'.$codeSuffix.random_int(100, 999),
        'full_name' => 'Nhân viên Accrual',
        'gender' => 'female',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900'.random_int(100000, 999999),
        'email' => 'accrual-'.$codeSuffix.random_int(1000, 9999).'@example.com',
        'hire_date' => $hireDate,
        'status' => 'active',
    ]);
}

test('monthly accrual command creates one day per eligible employee', function () {
    $employee = createAccrualEmployee('2025-01-01', 'A');

    $this->artisan('leave:accrue-monthly', [
        '--year' => 2026,
        '--month' => 8,
        '--force' => true,
    ])->assertSuccessful();

    $this->assertDatabaseHas('leave_accruals', [
        'employee_id' => $employee->id,
        'accrual_year' => 2026,
        'accrual_month' => 8,
        'days' => 1,
    ]);

    $this->assertDatabaseHas('leave_accrual_runs', [
        'accrual_year' => 2026,
        'accrual_month' => 8,
        'status' => 'completed',
        'accruals_created' => 1,
    ]);
});

test('monthly accrual is idempotent when run twice', function () {
    $employee = createAccrualEmployee('2026-07-01', 'B');
    $service = app(LeaveMonthlyAccrualService::class);

    $first = $service->accrueForMonth(2026, 7);
    $second = $service->accrueForMonth(2026, 7);

    expect($first['created'])->toBe(1)
        ->and($first['skipped'])->toBe(0)
        ->and($second['created'])->toBe(0)
        ->and($second['skipped'])->toBe(1)
        ->and(LeaveAccrual::query()->where('employee_id', $employee->id)->where('accrual_year', 2026)->count())->toBe(1);
});

test('mid month hire after cutoff does not accrue in hire month', function () {
    createAccrualEmployee('2026-07-20', 'C');

    app(LeaveMonthlyAccrualService::class)->accrueForMonth(2026, 7);

    expect(LeaveAccrual::query()->where('accrual_year', 2026)->where('accrual_month', 7)->count())->toBe(0);

    app(LeaveMonthlyAccrualService::class)->accrueForMonth(2026, 8);

    expect(LeaveAccrual::query()->where('accrual_year', 2026)->where('accrual_month', 8)->count())->toBe(1);
});

test('leave balance uses accrual ledger when records exist', function () {
    $employee = createAccrualEmployee('2026-07-01', 'D');

    app(LeaveMonthlyAccrualService::class)->accrueForMonth(2026, 7);
    app(LeaveMonthlyAccrualService::class)->accrueForMonth(2026, 8);

    $balance = app(LeaveBalanceService::class)->forEmployee($employee, Carbon::parse('2026-08-23'));

    expect($balance['annual_quota'])->toBe(2.0)
        ->and($balance['annual_remaining'])->toBe(2.0);
});

test('accrual command skips when not end of month without force', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-15 10:00:00'));

    $this->artisan('leave:accrue-monthly')
        ->assertSuccessful();

    expect(LeaveAccrualRun::query()->count())->toBe(0);

    Carbon::setTestNow();
});

test('accrual is blocked when cumulative unpaid leave exceeds yearly threshold', function () {
    $employee = createAccrualEmployee('2025-01-01', 'U');

    LeaveRequest::create([
        'employee_id' => $employee->id,
        'leave_type' => 'unpaid',
        'start_date' => '2026-08-04',
        'end_date' => '2026-08-22',
        'total_days' => 15,
        'reason' => 'Nghỉ không lương dài hạn',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $result = app(LeaveMonthlyAccrualService::class)->accrueForMonth(2026, 8);

    expect($result['blocked_unpaid'])->toBe(1)
        ->and($result['created'])->toBe(0)
        ->and(LeaveAccrual::query()->where('employee_id', $employee->id)->count())->toBe(0);
});

test('accrual is allowed when unpaid leave is within yearly threshold', function () {
    $employee = createAccrualEmployee('2025-01-01', 'V');

    LeaveRequest::create([
        'employee_id' => $employee->id,
        'leave_type' => 'unpaid',
        'start_date' => '2026-08-04',
        'end_date' => '2026-08-15',
        'total_days' => 10,
        'reason' => 'Nghỉ không lương ngắn',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $result = app(LeaveMonthlyAccrualService::class)->accrueForMonth(2026, 8);

    expect($result['blocked_unpaid'])->toBe(0)
        ->and($result['created'])->toBe(1)
        ->and(LeaveAccrual::query()->where('employee_id', $employee->id)->where('accrual_month', 8)->exists())->toBeTrue();
});

test('maternity leave month still accrues annual leave', function () {
    $employee = createAccrualEmployee('2025-01-01', 'M');

    LeaveRequest::create([
        'employee_id' => $employee->id,
        'leave_type' => 'maternity',
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-31',
        'total_days' => 21,
        'reason' => 'Nghỉ thai sản',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $result = app(LeaveMonthlyAccrualService::class)->accrueForMonth(2026, 8);

    expect($result['created'])->toBe(1)
        ->and(LeaveAccrual::query()->where('employee_id', $employee->id)->where('accrual_month', 8)->exists())->toBeTrue();
});

test('maternity leave still accrues even when unpaid threshold exceeded', function () {
    $employee = createAccrualEmployee('2025-01-01', 'M2');

    LeaveRequest::create([
        'employee_id' => $employee->id,
        'leave_type' => 'unpaid',
        'start_date' => '2026-01-05',
        'end_date' => '2026-01-30',
        'total_days' => 20,
        'reason' => 'Nghỉ không lương',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    LeaveRequest::create([
        'employee_id' => $employee->id,
        'leave_type' => 'maternity',
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-31',
        'total_days' => 21,
        'reason' => 'Nghỉ thai sản',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $result = app(LeaveMonthlyAccrualService::class)->accrueForMonth(2026, 8);

    expect($result['created'])->toBe(1)
        ->and($result['blocked_unpaid'])->toBe(0);
});

test('first two sick months in year still accrue third sick month is blocked', function () {
    $employee = createAccrualEmployee('2025-01-01', 'S');

    foreach ([6, 7, 8] as $month) {
        $start = sprintf('2026-%02d-02', $month);
        $end = sprintf('2026-%02d-25', $month);

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'sick',
            'start_date' => $start,
            'end_date' => $end,
            'total_days' => 18,
            'reason' => 'Nghỉ ốm BHXH',
            'status' => LeaveRequest::STATUS_APPROVED,
        ]);
    }

    $service = app(LeaveMonthlyAccrualService::class);

    expect($service->accrueForMonth(2026, 6)['created'])->toBe(1)
        ->and($service->accrueForMonth(2026, 7)['created'])->toBe(1)
        ->and($service->accrueForMonth(2026, 8)['blocked_sick'])->toBe(1)
        ->and($service->accrueForMonth(2026, 8)['created'])->toBe(0)
        ->and(LeaveAccrual::query()->where('employee_id', $employee->id)->count())->toBe(2);
});

test('accrual command runs on last day of month without force', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-31 23:45:00'));

    createAccrualEmployee('2025-01-01', 'E');

    $this->artisan('leave:accrue-monthly')
        ->assertSuccessful();

    expect(LeaveAccrualRun::query()->where('accrual_year', 2026)->where('accrual_month', 8)->exists())->toBeTrue();

    Carbon::setTestNow();
});
