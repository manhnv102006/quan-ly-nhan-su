<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\LeaveBalanceService;
use App\Support\LeaveAccrualRules;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);

    Config::set('leave.mid_month_cutoff_day', 15);
    Config::set('leave.mid_month_after_cutoff', LeaveAccrualRules::AFTER_CUTOFF_NEXT_MONTH);
});

function createEmployeeHiredOn(string $hireDate): Employee
{
    $user = User::factory()->create([
        'role_id' => Role::where('name', Role::EMPLOYEE)->value('id'),
        'status' => 'active',
    ]);

    return Employee::create([
        'user_id' => $user->id,
        'employee_code' => 'NV-PR-'.random_int(1000, 9999),
        'full_name' => 'Nhân viên Pro-rata',
        'gender' => 'female',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900'.random_int(100000, 999999),
        'email' => 'prorata-'.random_int(1000, 9999).'@example.com',
        'hire_date' => $hireDate,
        'status' => 'active',
    ]);
}

test('employee hired before calendar year receives full annual quota', function () {
    $employee = createEmployeeHiredOn('2025-06-01');
    $service = app(LeaveBalanceService::class);

    expect($service->annualQuotaForEmployee($employee, 2026, Carbon::parse('2026-08-23')))
        ->toBe(12.0);
});

test('employee hired on january first of same year accrues monthly not twelve days immediately', function () {
    $employee = createEmployeeHiredOn('2026-01-01');
    $service = app(LeaveBalanceService::class);

    expect($service->annualQuotaForEmployee($employee, 2026, Carbon::parse('2026-08-23')))
        ->toBe(8.0);
});

test('employee hired on or before cutoff day counts hire month', function () {
    $employee = createEmployeeHiredOn('2026-07-15');
    $service = app(LeaveBalanceService::class);

    expect($service->annualQuotaForEmployee($employee, 2026, Carbon::parse('2026-07-31')))
        ->toBe(1.0)
        ->and($service->annualQuotaForEmployee($employee, 2026, Carbon::parse('2026-08-23')))
        ->toBe(2.0)
        ->and($service->annualQuotaForEmployee($employee, 2026, Carbon::parse('2026-12-31')))
        ->toBe(6.0);
});

test('employee hired after cutoff day accrues from next month by default', function () {
    $employee = createEmployeeHiredOn('2026-07-16');
    $service = app(LeaveBalanceService::class);

    expect($service->annualQuotaForEmployee($employee, 2026, Carbon::parse('2026-07-31')))
        ->toBe(0.0)
        ->and($service->annualQuotaForEmployee($employee, 2026, Carbon::parse('2026-08-23')))
        ->toBe(1.0)
        ->and($service->annualQuotaForEmployee($employee, 2026, Carbon::parse('2026-12-31')))
        ->toBe(5.0);
});

test('employee hired after cutoff day accrues current month when configured', function () {
    Config::set('leave.mid_month_after_cutoff', LeaveAccrualRules::AFTER_CUTOFF_CURRENT_MONTH);

    $employee = createEmployeeHiredOn('2026-07-20');
    $service = app(LeaveBalanceService::class);

    expect($service->annualQuotaForEmployee($employee, 2026, Carbon::parse('2026-08-23')))
        ->toBe(2.0);
});

test('mid year hire balance reflects pro rata quota not twelve days immediately', function () {
    $employee = createEmployeeHiredOn('2026-07-01');

    $balance = app(LeaveBalanceService::class)->forEmployee($employee, Carbon::parse('2026-08-23'));

    expect($balance['annual_quota'])->toBe(2.0)
        ->and($balance['annual_is_prorated'])->toBeTrue()
        ->and($balance['annual_remaining'])->toBe(2.0);
});

test('annual leave approval is blocked when exceeding pro rata quota', function () {
    $employee = createEmployeeHiredOn('2026-07-01');

    LeaveRequest::create([
        'employee_id' => $employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-08-04',
        'end_date' => '2026-08-05',
        'total_days' => 2,
        'reason' => 'Phép năm',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $pending = LeaveRequest::create([
        'employee_id' => $employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-08-18',
        'end_date' => '2026-08-18',
        'total_days' => 1,
        'reason' => 'Phép năm thêm',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    $allowance = app(LeaveBalanceService::class)->annualQuotaForEmployee(
        $employee,
        2026,
        Carbon::parse('2026-08-18'),
    );

    expect($allowance)->toBe(2.0);

    expect(fn () => app(\App\Services\LeaveApprovalService::class)->approve(
        $pending,
        1,
    ))->toThrow(\Illuminate\Validation\ValidationException::class);
});
