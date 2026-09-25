<?php

use App\Models\Employee;
use App\Models\LeaveCarryOver;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\LeaveBalanceService;
use App\Services\LeaveCarryOverService;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow('2026-09-24');

    $this->employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $this->user = User::factory()->create([
        'role_id' => $this->employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee = Employee::create([
        'user_id' => $this->user->id,
        'employee_code' => 'NV-HUY-'.random_int(100, 999),
        'full_name' => 'Nhân viên hủy phép',
        'gender' => 'male',
        'date_of_birth' => '1995-01-01',
        'phone' => '0911'.random_int(100000, 999999),
        'email' => 'huy-'.random_int(1000, 9999).'@example.com',
        'hire_date' => '2025-01-01',
        'status' => 'active',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

function makeLeave(Employee $employee, array $overrides = []): LeaveRequest
{
    return LeaveRequest::create(array_merge([
        'employee_id' => $employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-07',
        'total_days' => 3,
        'reason' => 'Việc gia đình',
        'status' => LeaveRequest::STATUS_PENDING,
    ], $overrides));
}

test('employee cancels a pending leave and the reserved days return to the balance', function () {
    $leave = makeLeave($this->employee);
    $balance = app(LeaveBalanceService::class);
    $pendingBefore = $balance->forEmployee($this->employee)['annual_pending'];

    $response = $this->actingAs($this->user)->post(route('employee.leave-requests.cancel', $leave));

    $response->assertRedirect(route('employee.leave-requests.show', $leave));
    expect($leave->fresh()->status)->toBe(LeaveRequest::STATUS_CANCELLED)
        ->and($balance->forEmployee($this->employee)['annual_pending'])->toBe($pendingBefore - 3);
});

test('employee cancels an approved leave before it starts and the balance is refunded', function () {
    Carbon::setTestNow('2026-02-20');

    $carry = LeaveCarryOver::create([
        'employee_id' => $this->employee->id,
        'source_year' => 2025,
        'target_year' => 2026,
        'days' => 3,
        'days_used' => 0,
        'expires_at' => '2026-04-30',
        'status' => 'active',
    ]);

    $leave = makeLeave($this->employee, [
        'start_date' => '2026-03-02',
        'end_date' => '2026-03-04',
        'total_days' => 3,
        'status' => LeaveRequest::STATUS_APPROVED,
        'approved_at' => '2026-02-01 08:00:00',
    ]);
    app(LeaveCarryOverService::class)->consumeForApprovedLeave($leave->fresh(['employee']));
    expect($carry->fresh()->days_used)->toBe(3.0);

    $this->actingAs($this->user)->post(route('employee.leave-requests.cancel', $leave))
        ->assertRedirect();

    $balance = app(LeaveBalanceService::class)->forEmployee($this->employee, Carbon::parse('2026-02-20'));

    expect($leave->fresh()->status)->toBe(LeaveRequest::STATUS_CANCELLED)
        ->and($carry->fresh()->days_used)->toBe(0.0)
        ->and($carry->fresh()->status)->toBe('active')
        ->and($balance['annual_used'])->toBe(0.0);
});

test('employee cannot cancel an approved leave after it has started', function () {
    $leave = makeLeave($this->employee, [
        'start_date' => '2026-09-22',
        'end_date' => '2026-09-25',
        'total_days' => 4,
        'status' => LeaveRequest::STATUS_APPROVED,
        'approved_at' => '2026-09-20 08:00:00',
    ]);

    $this->actingAs($this->user)
        ->from(route('employee.leave-requests.show', $leave))
        ->post(route('employee.leave-requests.cancel', $leave))
        ->assertRedirect(route('employee.leave-requests.show', $leave))
        ->assertSessionHasErrors('leave_request');

    expect($leave->fresh()->status)->toBe(LeaveRequest::STATUS_APPROVED)
        ->and($leave->fresh()->end_date->toDateString())->toBe('2026-09-25')
        ->and($leave->fresh()->total_days)->toBe(4.0);
});

test('employee cannot cancel a pending leave whose start date has arrived', function () {
    $leave = makeLeave($this->employee, [
        'start_date' => '2026-09-24',
        'end_date' => '2026-09-25',
        'total_days' => 2,
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    $this->actingAs($this->user)
        ->from(route('employee.leave-requests'))
        ->post(route('employee.leave-requests.cancel', $leave))
        ->assertRedirect(route('employee.leave-requests'))
        ->assertSessionHasErrors('leave_request');

    expect($leave->fresh()->status)->toBe(LeaveRequest::STATUS_PENDING)
        ->and($leave->fresh()->total_days)->toBe(2.0);
});

test('employee cannot cancel an approved leave that is already fully taken', function () {
    $leave = makeLeave($this->employee, [
        'start_date' => '2026-09-21',
        'end_date' => '2026-09-23',
        'total_days' => 3,
        'status' => LeaveRequest::STATUS_APPROVED,
        'approved_at' => '2026-09-15 08:00:00',
    ]);

    $this->actingAs($this->user)
        ->from(route('employee.leave-requests.show', $leave))
        ->post(route('employee.leave-requests.cancel', $leave))
        ->assertRedirect(route('employee.leave-requests.show', $leave))
        ->assertSessionHasErrors('leave_request');

    expect($leave->fresh()->status)->toBe(LeaveRequest::STATUS_APPROVED)
        ->and($leave->fresh()->total_days)->toBe(3.0);
});

test('another employee cannot cancel the request', function () {
    $leave = makeLeave($this->employee);
    $other = User::factory()->create([
        'role_id' => $this->employeeRole->id,
        'status' => 'active',
    ]);
    Employee::create([
        'user_id' => $other->id,
        'employee_code' => 'NV-KHAC-'.random_int(100, 999),
        'full_name' => 'Người khác',
        'gender' => 'female',
        'date_of_birth' => '1996-01-01',
        'phone' => '0922'.random_int(100000, 999999),
        'email' => 'khac-'.random_int(1000, 9999).'@example.com',
        'hire_date' => '2025-01-01',
        'status' => 'active',
    ]);

    $this->actingAs($other)
        ->post(route('employee.leave-requests.cancel', $leave))
        ->assertForbidden();

    expect($leave->fresh()->status)->toBe(LeaveRequest::STATUS_PENDING);
});
