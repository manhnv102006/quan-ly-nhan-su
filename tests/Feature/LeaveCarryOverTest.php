<?php

use App\Models\Employee;
use App\Models\LeaveAccrual;
use App\Models\LeaveCarryOver;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\LeaveBalanceService;
use App\Services\LeaveCarryOverService;
use Carbon\Carbon;

beforeEach(function () {
    Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);

    config([
        'leave.carry_over_enabled' => true,
        'leave.carry_over_expiry_month' => 4,
    ]);
});

function createCarryOverEmployee(string $hireDate = '2025-01-01'): Employee
{
    $user = User::factory()->create([
        'role_id' => Role::where('name', Role::EMPLOYEE)->value('id'),
        'status' => 'active',
    ]);

    return Employee::create([
        'user_id' => $user->id,
        'employee_code' => 'NV-CO-'.random_int(1000, 9999),
        'full_name' => 'Nhân viên Carry Over',
        'gender' => 'female',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900'.random_int(100000, 999999),
        'email' => 'carry-'.random_int(1000, 9999).'@example.com',
        'hire_date' => $hireDate,
        'status' => 'active',
    ]);
}

test('carry over command transfers unused annual leave to next year', function () {
    $employee = createCarryOverEmployee();

    foreach (range(1, 10) as $month) {
        LeaveAccrual::create([
            'employee_id' => $employee->id,
            'accrual_year' => 2025,
            'accrual_month' => $month,
            'days' => 1,
            'source' => 'scheduled',
            'accrued_at' => now(),
        ]);
    }

    LeaveRequest::create([
        'employee_id' => $employee->id,
        'leave_type' => 'annual',
        'start_date' => '2025-06-02',
        'end_date' => '2025-06-06',
        'total_days' => 5,
        'reason' => 'Phép năm',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $result = app(LeaveCarryOverService::class)->processCarryOverForSourceYear(2025);

    expect($result['created'])->toBe(1);

    $this->assertDatabaseHas('leave_carry_overs', [
        'employee_id' => $employee->id,
        'source_year' => 2025,
        'target_year' => 2026,
        'days' => 5,
        'status' => 'active',
    ]);
});

test('carried over leave appears in balance before expiry', function () {
    $employee = createCarryOverEmployee();

    LeaveCarryOver::create([
        'employee_id' => $employee->id,
        'source_year' => 2025,
        'target_year' => 2026,
        'days' => 3,
        'days_used' => 0,
        'expires_at' => '2026-04-30',
        'status' => 'active',
    ]);

    foreach (range(1, 2) as $month) {
        LeaveAccrual::create([
            'employee_id' => $employee->id,
            'accrual_year' => 2026,
            'accrual_month' => $month,
            'days' => 1,
            'source' => 'scheduled',
            'accrued_at' => now(),
        ]);
    }

    $balance = app(LeaveBalanceService::class)->forEmployee($employee, Carbon::parse('2026-03-15'));

    expect($balance['carried_over_remaining'])->toBe(3.0)
        ->and($balance['annual_quota'])->toBe(2.0)
        ->and($balance['annual_remaining'])->toBe(5.0);
});

test('carried over leave expires after configured month', function () {
    $employee = createCarryOverEmployee();

    LeaveCarryOver::create([
        'employee_id' => $employee->id,
        'source_year' => 2025,
        'target_year' => 2026,
        'days' => 4,
        'days_used' => 0,
        'expires_at' => '2026-04-30',
        'status' => 'active',
    ]);

    LeaveAccrual::create([
        'employee_id' => $employee->id,
        'accrual_year' => 2026,
        'accrual_month' => 5,
        'days' => 1,
        'source' => 'scheduled',
        'accrued_at' => now(),
    ]);

    $balance = app(LeaveBalanceService::class)->forEmployee($employee, Carbon::parse('2026-05-10'));

    expect($balance['carried_over_remaining'])->toBe(0.0)
        ->and($balance['annual_remaining'])->toBe(1.0);

    $this->assertDatabaseHas('leave_carry_overs', [
        'employee_id' => $employee->id,
        'status' => 'expired',
    ]);
});

test('approved annual leave consumes carried over days first', function () {
    $employee = createCarryOverEmployee();

    $carry = LeaveCarryOver::create([
        'employee_id' => $employee->id,
        'source_year' => 2025,
        'target_year' => 2026,
        'days' => 3,
        'days_used' => 0,
        'expires_at' => '2026-04-30',
        'status' => 'active',
    ]);

    $leave = LeaveRequest::create([
        'employee_id' => $employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-02-10',
        'end_date' => '2026-02-12',
        'total_days' => 2,
        'reason' => 'Phép năm',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    app(\App\Services\LeaveCarryOverService::class)->consumeForApprovedLeave(
        $leave->fresh(['employee'])
    );

    expect($carry->fresh()->days_used)->toBe(2.0)
        ->and($carry->fresh()->status)->toBe('active');
});
