<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\NotificationUser;
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
        'employee_code' => 'NV-B7-'.random_int(100, 999),
        'full_name' => 'Nhân viên B7',
        'gender' => 'male',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900'.random_int(100000, 999999),
        'email' => 'b7-'.random_int(1000, 9999).'@example.com',
        'hire_date' => '2025-01-01',
        'status' => 'active',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

function annualLeavePayload(array $overrides = []): array
{
    return array_merge([
        'leave_type' => 'annual',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-08',
        'reason' => 'Việc gia đình',
    ], $overrides);
}

test('B7: annual leave within balance creates a single request', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-03-02',
        'end_date' => '2026-03-10',
        'total_days' => 7,
        'reason' => 'Phép năm',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        annualLeavePayload(),
    );

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.leave-requests'));
    $response->assertSessionHas('success', 'Tạo đơn xin nghỉ phép thành công.');
    expect(LeaveRequest::query()->where('employee_id', $this->employee->id)->where('status', LeaveRequest::STATUS_PENDING)->count())->toBe(1);
});

test('B7: annual leave exceeding balance is split into paid and unpaid requests', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-03-02',
        'end_date' => '2026-03-12',
        'total_days' => 11,
        'reason' => 'Phép năm',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        annualLeavePayload(),
    );

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.leave-requests'));
    $response->assertSessionHas(
        'success',
        'Bạn xin nghỉ 3 ngày nhưng chỉ còn 1 ngày hưởng lương. 2 ngày không được hưởng lương và đã được tách thành đơn nghỉ không lương.',
    );

    $pending = LeaveRequest::query()
        ->where('employee_id', $this->employee->id)
        ->where('status', LeaveRequest::STATUS_PENDING)
        ->orderBy('start_date')
        ->get();

    expect($pending)->toHaveCount(2)
        ->and($pending[0]->leave_type)->toBe('annual')
        ->and((float) $pending[0]->total_days)->toBe(1.0)
        ->and($pending[0]->start_date->toDateString())->toBe('2026-10-06')
        ->and($pending[1]->leave_type)->toBe('unpaid')
        ->and((float) $pending[1]->total_days)->toBe(2.0)
        ->and($pending[1]->start_date->toDateString())->toBe('2026-10-07')
        ->and($pending[1]->reason)->toContain('Phần vượt số dư phép năm');

    $notification = Notification::query()->where('title', 'Đơn nghỉ vượt số ngày hưởng lương')->first();

    expect($notification)->not->toBeNull()
        ->and($notification->content)->toContain('2 ngày không được hưởng lương');

    expect(
        NotificationUser::query()
            ->where('notification_id', $notification->id)
            ->where('user_id', $this->user->id)
            ->exists()
    )->toBeTrue();
});

test('B7: annual leave preview warns when requested days exceed paid balance', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-03-02',
        'end_date' => '2026-03-12',
        'total_days' => 11,
        'reason' => 'Phép năm',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $response = $this->actingAs($this->user)->getJson(route('employee.leave-requests.paid-balance-preview', [
        'leave_type' => 'annual',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-08',
    ]));

    $response->assertOk()
        ->assertJsonPath('split', true)
        ->assertJsonPath('paid_days', 1)
        ->assertJsonPath('unpaid_days', 2)
        ->assertJsonPath('message', 'Bạn xin nghỉ 3 ngày nhưng chỉ còn 1 ngày hưởng lương. 2 ngày không được hưởng lương và sẽ được tách thành đơn nghỉ không lương nếu bạn gửi đơn.');
});

test('B7: annual leave with zero balance is blocked', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-02-02',
        'end_date' => '2026-02-13',
        'total_days' => 12,
        'reason' => 'Hết phép',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        annualLeavePayload([
            'start_date' => '2026-10-06',
            'end_date' => '2026-10-06',
        ]),
    );

    $response->assertSessionHasErrors('leave_type');
    $response->assertSessionHasErrors([
        'leave_type' => 'Bạn không còn số dư phép năm. Vui lòng chọn loại Nghỉ không lương hoặc rút ngắn thời gian nghỉ.',
    ]);
    expect(LeaveRequest::query()->where('employee_id', $this->employee->id)->where('status', LeaveRequest::STATUS_PENDING)->count())->toBe(0);
});

test('B8: pending annual leave reduces available balance when submitting', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-03-02',
        'end_date' => '2026-03-12',
        'total_days' => 11,
        'reason' => 'Phép năm',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-02',
        'total_days' => 1,
        'reason' => 'Chờ duyệt',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        annualLeavePayload([
            'start_date' => '2026-10-06',
            'end_date' => '2026-10-06',
        ]),
    );

    $response->assertSessionHasErrors('leave_type');
    $response->assertSessionHasErrors([
        'leave_type' => 'Bạn không còn số dư phép năm. Vui lòng chọn loại Nghỉ không lương hoặc rút ngắn thời gian nghỉ.',
    ]);
});

test('B8: submission succeeds when remaining balance covers pending and new request', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-03-02',
        'end_date' => '2026-03-09',
        'total_days' => 10,
        'reason' => 'Phép năm',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-02',
        'total_days' => 1,
        'reason' => 'Chờ duyệt',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        annualLeavePayload([
            'start_date' => '2026-10-06',
            'end_date' => '2026-10-06',
        ]),
    );

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.leave-requests'));
    expect(LeaveRequest::query()
        ->where('employee_id', $this->employee->id)
        ->where('status', LeaveRequest::STATUS_PENDING)
        ->count())->toBe(2);
});

test('B8: pending annual leave causes split when new request exceeds remaining balance', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-03-02',
        'end_date' => '2026-03-11',
        'total_days' => 10,
        'reason' => 'Phép năm',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-03',
        'total_days' => 1,
        'reason' => 'Chờ duyệt',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        annualLeavePayload(),
    );

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas(
        'success',
        'Bạn xin nghỉ 3 ngày nhưng chỉ còn 1 ngày hưởng lương. 2 ngày không được hưởng lương và đã được tách thành đơn nghỉ không lương.',
    );
});

test('B7: unpaid leave is not limited by annual balance', function () {
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => '2026-02-02',
        'end_date' => '2026-02-13',
        'total_days' => 12,
        'reason' => 'Hết phép',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        annualLeavePayload([
            'leave_type' => 'unpaid',
            'start_date' => '2026-10-06',
            'end_date' => '2026-10-08',
        ]),
    );

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.leave-requests'));
    expect(LeaveRequest::query()
        ->where('employee_id', $this->employee->id)
        ->where('leave_type', 'unpaid')
        ->where('status', LeaveRequest::STATUS_PENDING)
        ->count())->toBe(1);
});
