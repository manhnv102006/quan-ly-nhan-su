<?php

use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow('2026-09-21');

    $this->employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $this->user = User::factory()->create([
        'role_id' => $this->employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee = Employee::create([
        'user_id' => $this->user->id,
        'employee_code' => 'NV-HD-'.random_int(100, 999),
        'full_name' => 'Nhân viên nửa ngày',
        'gender' => 'male',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900'.random_int(100000, 999999),
        'email' => 'halfday-'.random_int(1000, 9999).'@example.com',
        'hire_date' => '2025-01-01',
        'status' => 'active',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

function halfDayPayload(array $overrides = []): array
{
    return array_merge([
        'leave_type' => 'half_day',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-06',
        'half_day_period' => 'morning',
        'reason' => 'Việc riêng buổi sáng',
    ], $overrides);
}

test('half day leave without period is blocked', function () {
    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        halfDayPayload(['half_day_period' => '']),
    );

    $response->assertSessionHasErrors('half_day_period');
    $this->assertDatabaseMissing('leave_requests', [
        'employee_id' => $this->employee->id,
        'leave_type' => 'half_day',
    ]);
});

test('half day leave spanning multiple days is blocked', function () {
    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        halfDayPayload([
            'start_date' => '2026-10-06',
            'end_date' => '2026-10-07',
        ]),
    );

    $response->assertSessionHasErrors('end_date');
});

test('half day leave on Sunday is blocked', function () {
    expect(Carbon::parse('2026-10-04')->isSunday())->toBeTrue();

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        halfDayPayload([
            'start_date' => '2026-10-04',
            'end_date' => '2026-10-04',
        ]),
    );

    $response->assertSessionHasErrors('start_date');
});

test('half day leave on holiday is blocked', function () {
    Holiday::create([
        'name' => 'Nghỉ lễ test',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-06',
        'type' => 'public_holiday',
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        halfDayPayload(),
    );

    $response->assertSessionHasErrors('start_date');
    $this->assertDatabaseMissing('leave_requests', [
        'employee_id' => $this->employee->id,
        'leave_type' => 'half_day',
    ]);
});

test('half day morning leave deducts 0.5 day', function () {
    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        halfDayPayload(['half_day_period' => 'morning']),
    );

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.leave-requests'));

    $this->assertDatabaseHas('leave_requests', [
        'employee_id' => $this->employee->id,
        'leave_type' => 'half_day',
        'half_day_period' => 'morning',
        'total_days' => 0.5,
    ]);
});

test('half day afternoon leave deducts 0.5 day', function () {
    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        halfDayPayload(['half_day_period' => 'afternoon']),
    );

    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('leave_requests', [
        'employee_id' => $this->employee->id,
        'leave_type' => 'half_day',
        'half_day_period' => 'afternoon',
        'total_days' => 0.5,
    ]);
});

test('B11: half day leave is allowed when another half day exists on a different period', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'half_day',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-06',
        'half_day_period' => 'morning',
        'total_days' => 0.5,
        'reason' => 'Nghỉ sáng',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        halfDayPayload(['half_day_period' => 'afternoon']),
    );

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.leave-requests'));
    expect(LeaveRequest::query()->where('employee_id', $this->employee->id)->count())->toBe(2);
});

test('B11: half day leave is blocked when another half day exists on the same period', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'half_day',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-06',
        'half_day_period' => 'morning',
        'total_days' => 0.5,
        'reason' => 'Nghỉ sáng',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        halfDayPayload(['half_day_period' => 'morning']),
    );

    $response->assertSessionHasErrors('start_date');
    expect(LeaveRequest::query()->where('employee_id', $this->employee->id)->count())->toBe(1);
});

test('B11: half day leave is blocked when a full-day leave exists on the same date', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'unpaid',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-06',
        'total_days' => 1,
        'reason' => 'Nghỉ cả ngày',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        halfDayPayload(['half_day_period' => 'afternoon']),
    );

    $response->assertSessionHasErrors('start_date');
    expect(LeaveRequest::query()->where('employee_id', $this->employee->id)->count())->toBe(1);
});
