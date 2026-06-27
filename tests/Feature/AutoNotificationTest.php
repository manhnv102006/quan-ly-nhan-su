<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\Role;
use App\Models\User;
use App\Services\AutoNotificationService;

beforeEach(function () {
    $this->adminRole = Role::create(['name' => Role::ADMIN, 'description' => 'Admin']);
    $this->employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);

    $this->admin = User::factory()->create([
        'role_id' => $this->adminRole->id,
        'status' => 'active',
    ]);

    $this->employeeUser = User::factory()->create([
        'role_id' => $this->employeeRole->id,
        'status' => 'active',
    ]);

    $this->employee = Employee::create([
        'user_id' => $this->employeeUser->id,
        'employee_code' => 'NV-AUTO-001',
        'full_name' => 'Nguyễn Văn Test',
        'gender' => 'male',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900000002',
        'email' => 'auto-employee@example.com',
        'hire_date' => now()->toDateString(),
        'status' => 'active',
    ]);
});

test('auto notification is sent to admin when leave request is submitted', function () {
    $leaveRequest = LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => now()->addDays(3)->toDateString(),
        'end_date' => now()->addDays(5)->toDateString(),
        'total_days' => 3,
        'reason' => 'Nghỉ phép cá nhân',
        'status' => 'pending',
    ]);

    app(AutoNotificationService::class)->leaveSubmitted($leaveRequest);

    $notification = Notification::query()->where('title', 'Đơn nghỉ phép mới')->first();

    expect($notification)->not->toBeNull()
        ->and($notification->type)->toBe('leave')
        ->and($notification->sender_id)->toBeNull();

    expect(
        NotificationUser::query()
            ->where('notification_id', $notification->id)
            ->where('user_id', $this->admin->id)
            ->exists()
    )->toBeTrue();
});

test('auto notification is sent to employee when leave request is approved', function () {
    $leaveRequest = LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'annual',
        'start_date' => now()->addDays(3)->toDateString(),
        'end_date' => now()->addDays(5)->toDateString(),
        'total_days' => 3,
        'reason' => 'Nghỉ phép cá nhân',
        'status' => 'approved',
        'approved_by' => $this->admin->id,
        'approved_at' => now(),
    ]);

    app(AutoNotificationService::class)->leaveApproved($leaveRequest);

    $notification = Notification::query()->where('title', 'Đơn nghỉ phép đã được duyệt')->first();

    expect($notification)->not->toBeNull();

    expect(
        NotificationUser::query()
            ->where('notification_id', $notification->id)
            ->where('user_id', $this->employeeUser->id)
            ->where('is_read', false)
            ->exists()
    )->toBeTrue();
});

test('employee does not receive their own leave submission notification', function () {
    $adminEmployee = Employee::create([
        'user_id' => $this->admin->id,
        'employee_code' => 'NV-ADMIN-001',
        'full_name' => 'Admin Employee',
        'gender' => 'male',
        'date_of_birth' => '1990-01-01',
        'phone' => '0900000003',
        'email' => 'admin-employee@example.com',
        'hire_date' => now()->toDateString(),
        'status' => 'active',
    ]);

    $leaveRequest = LeaveRequest::create([
        'employee_id' => $adminEmployee->id,
        'leave_type' => 'annual',
        'start_date' => now()->addDays(3)->toDateString(),
        'end_date' => now()->addDays(5)->toDateString(),
        'total_days' => 3,
        'reason' => 'Admin xin nghỉ',
        'status' => 'pending',
    ]);

    app(AutoNotificationService::class)->leaveSubmitted($leaveRequest);

    $notification = Notification::query()->where('title', 'Đơn nghỉ phép mới')->latest('id')->first();

    expect($notification)->not->toBeNull();

    expect(
        NotificationUser::query()
            ->where('notification_id', $notification->id)
            ->where('user_id', $this->admin->id)
            ->exists()
    )->toBeFalse();
});

test('authenticated users can open shared notifications page', function () {
    $this->actingAs($this->employeeUser)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Tất cả thông báo');
});
