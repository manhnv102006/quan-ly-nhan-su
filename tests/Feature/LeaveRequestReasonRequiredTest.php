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
        'employee_code' => 'NV-B1-'.random_int(100, 999),
        'full_name' => 'Nhân viên B1',
        'gender' => 'male',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900'.random_int(100000, 999999),
        'email' => 'b1-'.random_int(1000, 9999).'@example.com',
        'hire_date' => '2025-01-01',
        'status' => 'active',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

function validLeavePayload(array $overrides = []): array
{
    return array_merge([
        'leave_type' => 'unpaid',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-06',
        'reason' => 'Việc gia đình',
    ], $overrides);
}

test('B1: leave request without reason is blocked', function () {
    $payload = validLeavePayload();
    unset($payload['reason']);

    $response = $this->actingAs($this->user)->post(route('employee.leave-requests.store'), $payload);

    $response->assertSessionHasErrors('reason');
    $response->assertSessionHasErrors([
        'reason' => 'Vui lòng nhập lý do xin nghỉ phép.',
    ]);
    $this->assertDatabaseMissing('leave_requests', [
        'employee_id' => $this->employee->id,
    ]);
});

test('B1: leave request with empty reason is blocked', function () {
    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        validLeavePayload(['reason' => '']),
    );

    $response->assertSessionHasErrors('reason');
    $this->assertDatabaseMissing('leave_requests', [
        'employee_id' => $this->employee->id,
    ]);
});

test('B1: leave request with whitespace-only reason is blocked', function () {
    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        validLeavePayload(['reason' => '   ']),
    );

    $response->assertSessionHasErrors('reason');
    $this->assertDatabaseMissing('leave_requests', [
        'employee_id' => $this->employee->id,
    ]);
});

test('B2: leave request with start date after end date is blocked', function () {
    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        validLeavePayload([
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-06',
        ]),
    );

    $response->assertSessionHasErrors('end_date');
    $response->assertSessionHasErrors([
        'end_date' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
    ]);
    $this->assertDatabaseMissing('leave_requests', [
        'employee_id' => $this->employee->id,
    ]);
});

test('B2: leave request with same start and end date is allowed', function () {
    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        validLeavePayload([
            'start_date' => '2026-10-06',
            'end_date' => '2026-10-06',
        ]),
    );

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.leave-requests'));
    expect(LeaveRequest::query()->where('employee_id', $this->employee->id)->count())->toBe(1);
});

test('B3: leave request with start date in the past is blocked', function () {
    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        validLeavePayload([
            'start_date' => '2026-09-18',
            'end_date' => '2026-09-18',
        ]),
    );

    $response->assertSessionHasErrors('start_date');
    $response->assertSessionHasErrors([
        'start_date' => 'Không thể xin nghỉ trong quá khứ.',
    ]);
    $this->assertDatabaseMissing('leave_requests', [
        'employee_id' => $this->employee->id,
    ]);
});

test('B3: leave request starting today is allowed', function () {
    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        validLeavePayload([
            'start_date' => '2026-09-21',
            'end_date' => '2026-09-21',
        ]),
    );

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.leave-requests'));
    expect(LeaveRequest::query()->where('employee_id', $this->employee->id)->count())->toBe(1);
});

test('leave request overlapping an approved request is blocked', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-08',
        'total_days' => 3,
        'reason' => 'Đã duyệt',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        validLeavePayload([
            'start_date' => '2026-10-07',
            'end_date' => '2026-10-09',
        ]),
    );

    $response->assertSessionHasErrors('start_date');
    $response->assertSessionHasErrors([
        'start_date' => 'Khoảng nghỉ 07/10/2026 → 09/10/2026 trùng với đơn nghỉ phép đã duyệt hoặc đang chờ duyệt.',
    ]);
    expect(LeaveRequest::query()->where('employee_id', $this->employee->id)->count())->toBe(1);
});

test('leave request overlapping a pending request is blocked', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'sick',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-06',
        'total_days' => 1,
        'reason' => 'Chờ duyệt',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        validLeavePayload([
            'leave_type' => 'unpaid',
            'start_date' => '2026-10-06',
            'end_date' => '2026-10-06',
        ]),
    );

    $response->assertSessionHasErrors('start_date');
    expect(LeaveRequest::query()->where('employee_id', $this->employee->id)->count())->toBe(1);
});

test('leave request overlapping a rejected request is allowed', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-06',
        'total_days' => 1,
        'reason' => 'Bị từ chối',
        'status' => LeaveRequest::STATUS_REJECTED,
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        validLeavePayload([
            'start_date' => '2026-10-06',
            'end_date' => '2026-10-06',
        ]),
    );

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.leave-requests'));
    expect(LeaveRequest::query()->where('employee_id', $this->employee->id)->count())->toBe(2);
});

test('leave request on adjacent non overlapping dates is allowed', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-06',
        'total_days' => 1,
        'reason' => 'Đã duyệt',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        validLeavePayload([
            'start_date' => '2026-10-07',
            'end_date' => '2026-10-07',
        ]),
    );

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.leave-requests'));
    expect(LeaveRequest::query()->where('employee_id', $this->employee->id)->count())->toBe(2);
});

test('B6: leave request on Sunday only is blocked because total days is zero', function () {
    expect(Carbon::parse('2026-10-04')->isSunday())->toBeTrue();

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        validLeavePayload([
            'start_date' => '2026-10-04',
            'end_date' => '2026-10-04',
        ]),
    );

    $response->assertSessionHasErrors('start_date');
    $response->assertSessionHasErrors([
        'start_date' => 'Khoảng thời gian bạn chọn toàn bộ là ngày nghỉ/ngày Lễ. Vui lòng chọn lại.',
    ]);
    $this->assertDatabaseMissing('leave_requests', [
        'employee_id' => $this->employee->id,
    ]);
});

test('B6: leave request on holidays only is blocked because total days is zero', function () {
    Holiday::create([
        'name' => 'Nghỉ lễ test',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-08',
        'type' => 'public_holiday',
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        validLeavePayload([
            'start_date' => '2026-10-06',
            'end_date' => '2026-10-08',
        ]),
    );

    $response->assertSessionHasErrors('start_date');
    $this->assertDatabaseMissing('leave_requests', [
        'employee_id' => $this->employee->id,
    ]);
});

test('B6: leave request spanning only Sundays and holidays is blocked', function () {
    Holiday::create([
        'name' => 'Cuối tuần dài',
        'start_date' => '2026-10-03',
        'end_date' => '2026-10-04',
        'type' => 'public_holiday',
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        validLeavePayload([
            'start_date' => '2026-10-03',
            'end_date' => '2026-10-04',
        ]),
    );

    $response->assertSessionHasErrors('start_date');
    $this->assertDatabaseMissing('leave_requests', [
        'employee_id' => $this->employee->id,
    ]);
});

test('B6: leave request with at least one working day in range is allowed', function () {
    Holiday::create([
        'name' => 'Nghỉ lễ một phần',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-07',
        'type' => 'public_holiday',
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        validLeavePayload([
            'start_date' => '2026-10-06',
            'end_date' => '2026-10-08',
        ]),
    );

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.leave-requests'));
    expect(LeaveRequest::query()->where('employee_id', $this->employee->id)->value('total_days'))->toBe(1.0);
});
