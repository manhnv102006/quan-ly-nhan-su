<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Carbon\Carbon;

beforeEach(function () {
    $this->employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $this->user = User::factory()->create([
        'role_id' => $this->employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee = Employee::create([
        'user_id' => $this->user->id,
        'employee_code' => 'NV-LEAVE-001',
        'full_name' => 'Nhân viên Phép',
        'gender' => 'male',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900000011',
        'email' => 'leave-balance@example.com',
        'hire_date' => '2025-01-01',
        'status' => 'active',
    ]);
});

test('paid leave balance subtracts approved monthly and annual days', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'half_day',
        'start_date' => '2026-08-10',
        'end_date' => '2026-08-10',
        'total_days' => 0.5,
        'reason' => 'Việc riêng',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-03-02',
        'end_date' => '2026-03-04',
        'total_days' => 3,
        'reason' => 'Phép năm',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-08-20',
        'end_date' => '2026-08-20',
        'total_days' => 1,
        'reason' => 'Chờ duyệt',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    $balance = app(LeaveBalanceService::class)->forEmployee($this->employee, Carbon::parse('2026-08-23'));

    expect($balance['monthly_used'])->toBe(0.5)
        ->and($balance['monthly_remaining'])->toBe(0.5)
        ->and($balance['monthly_pending'])->toBe(1.0)
        ->and($balance['annual_used'])->toBe(3.0)
        ->and($balance['annual_remaining'])->toBe(9.0)
        ->and($balance['annual_pending'])->toBe(1.0);
});

test('bhxh and statutory company leave do not consume the monthly paid quota', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'sick',
        'start_date' => '2026-08-03',
        'end_date' => '2026-08-07',
        'total_days' => 5,
        'reason' => 'Ốm BHXH',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'wedding',
        'start_date' => '2026-08-10',
        'end_date' => '2026-08-12',
        'total_days' => 3,
        'reason' => 'Kết hôn',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $balance = app(LeaveBalanceService::class)->forEmployee($this->employee, Carbon::parse('2026-08-23'));

    expect($balance['monthly_used'])->toBe(0.0)
        ->and($balance['monthly_remaining'])->toBe(1.0)
        ->and($balance['annual_used'])->toBe(0.0);
});
