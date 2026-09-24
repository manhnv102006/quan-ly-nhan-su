<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Position;
use App\Models\Role;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow('2026-09-21 10:00:00');

    Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);

    $department = Department::create([
        'department_code' => 'PB-EL-'.random_int(100, 999),
        'department_name' => 'Phòng về sớm test',
        'max_employees' => 20,
        'status' => 'active',
    ]);

    $position = Position::create([
        'position_name' => 'Nhân viên',
        'description' => null,
        'base_salary' => 10000000,
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'role_id' => Role::where('name', Role::EMPLOYEE)->value('id'),
        'status' => 'active',
    ]);

    $this->employee = Employee::create([
        'user_id' => $user->id,
        'employee_code' => 'NV-EL-'.random_int(1000, 9999),
        'full_name' => 'Nhân viên về sớm test',
        'gender' => 'male',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900'.random_int(100000, 999999),
        'email' => 'early-leave-'.random_int(1000, 9999).'@example.com',
        'department_id' => $department->id,
        'position_id' => $position->id,
        'hire_date' => '2025-01-01',
        'status' => 'active',
    ]);

    $this->user = $user;

    $this->shift = Shift::create([
        'shift_name' => 'Ca chiều',
        'start_time' => '13:00:00',
        'end_time' => '17:00:00',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

function earlyLeavePayload(array $overrides = []): array
{
    return array_merge([
        'request_date' => '2026-09-21',
        'leave_time' => '16:00',
        'reason' => 'Có việc gia đình khẩn cấp cần xử lý trong chiều nay.',
    ], $overrides);
}

test('employee cannot submit early leave when selected date has no assigned shift', function () {
    $response = $this->actingAs($this->user)->post(route('employee.early-leave.store'), earlyLeavePayload());

    $response->assertSessionHasErrors('request_date');
    expect(session('errors')->get('request_date')[0])->toContain('không có ca làm');
    $this->assertDatabaseCount('early_leave_requests', 0);
});

test('employee can submit early leave when selected date has an assigned shift', function () {
    EmployeeShift::create([
        'employee_id' => $this->employee->id,
        'shift_id' => $this->shift->id,
        'work_date' => '2026-09-21',
    ]);

    $response = $this->actingAs($this->user)->post(route('employee.early-leave.store'), earlyLeavePayload());

    $response->assertRedirect(route('employee.early-leave.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('early_leave_requests', [
        'employee_id' => $this->employee->id,
        'request_date' => '2026-09-21 00:00:00',
        'leave_time' => '16:00',
        'status' => 'pending',
    ]);
});

test('employee cannot submit early leave when leave time is outside assigned shift', function () {
    EmployeeShift::create([
        'employee_id' => $this->employee->id,
        'shift_id' => $this->shift->id,
        'work_date' => '2026-09-21',
    ]);

    $response = $this->actingAs($this->user)->post(route('employee.early-leave.store'), earlyLeavePayload([
        'leave_time' => '12:30',
    ]));

    $response->assertSessionHasErrors('leave_time');
    expect(session('errors')->get('leave_time')[0])->toContain('trong ca làm');
    $this->assertDatabaseCount('early_leave_requests', 0);
});
