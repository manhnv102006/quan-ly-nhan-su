<?php

use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Role;
use App\Models\Shift;
use App\Models\User;

beforeEach(function () {
    $this->adminRole = Role::create(['name' => Role::ADMIN, 'description' => 'Admin']);
    $this->admin = User::factory()->create([
        'role_id' => $this->adminRole->id,
        'status' => 'active',
    ]);

    $this->employee = Employee::create([
        'employee_code' => 'NV-SHIFT-001',
        'full_name' => 'Nguyễn Văn Ca',
        'gender' => 'male',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900000100',
        'email' => 'shift-test@example.com',
        'hire_date' => now()->toDateString(),
        'status' => 'active',
    ]);

    $this->morningShift = Shift::create([
        'shift_name' => 'Ca sáng test',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    $this->afternoonShift = Shift::create([
        'shift_name' => 'Ca chiều test',
        'start_time' => '13:00:00',
        'end_time' => '17:00:00',
    ]);

    $this->overlapShift = Shift::create([
        'shift_name' => 'Ca trùng giờ test',
        'start_time' => '10:00:00',
        'end_time' => '14:00:00',
    ]);
});

test('admin can assign multiple non overlapping shifts on the same day', function () {
    $workDate = now()->addDay()->toDateString();

    $this->actingAs($this->admin)
        ->post(route('admin.employee-shifts.store'), [
            'assignment_scope' => 'employee',
            'period_mode' => 'single',
            'employee_id' => $this->employee->id,
            'shift_id' => $this->morningShift->id,
            'work_date' => $workDate,
        ])
        ->assertRedirect(route('admin.employee-shifts.index'));

    $response = $this->actingAs($this->admin)
        ->post(route('admin.employee-shifts.store'), [
            'assignment_scope' => 'employee',
            'period_mode' => 'single',
            'employee_id' => $this->employee->id,
            'shift_id' => $this->afternoonShift->id,
            'work_date' => $workDate,
        ]);

    $response->assertRedirect(route('admin.employee-shifts.index'));

    expect(EmployeeShift::query()
        ->where('employee_id', $this->employee->id)
        ->whereDate('work_date', $workDate)
        ->count())->toBe(2);
});

test('admin cannot assign overlapping shifts on the same day', function () {
    $workDate = now()->addDay()->toDateString();

    EmployeeShift::create([
        'employee_id' => $this->employee->id,
        'shift_id' => $this->morningShift->id,
        'work_date' => $workDate,
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.employee-shifts.store'), [
            'assignment_scope' => 'employee',
            'period_mode' => 'single',
            'employee_id' => $this->employee->id,
            'shift_id' => $this->overlapShift->id,
            'work_date' => $workDate,
        ]);

    $response->assertSessionHasErrors('shift_id');

    expect(EmployeeShift::query()
        ->where('employee_id', $this->employee->id)
        ->whereDate('work_date', $workDate)
        ->count())->toBe(1);
});

test('assigning the same shift again does not create duplicate rows', function () {
    $workDate = now()->addDay()->toDateString();

    $payload = [
        'assignment_scope' => 'employee',
        'period_mode' => 'single',
        'employee_id' => $this->employee->id,
        'shift_id' => $this->morningShift->id,
        'work_date' => $workDate,
    ];

    $this->actingAs($this->admin)->post(route('admin.employee-shifts.store'), $payload);
    $this->actingAs($this->admin)->post(route('admin.employee-shifts.store'), $payload);

    expect(EmployeeShift::query()
        ->where('employee_id', $this->employee->id)
        ->whereDate('work_date', $workDate)
        ->count())->toBe(1);
});

test('admin cannot assign overlapping shifts like 7h-9h and 8h-10h', function () {
    $workDate = now()->addDays(2)->toDateString();

    $shiftA = Shift::create([
        'shift_name' => 'Ca A',
        'start_time' => '07:00:00',
        'end_time' => '09:00:00',
    ]);

    $shiftB = Shift::create([
        'shift_name' => 'Ca B',
        'start_time' => '08:00:00',
        'end_time' => '10:00:00',
    ]);

    EmployeeShift::create([
        'employee_id' => $this->employee->id,
        'shift_id' => $shiftA->id,
        'work_date' => $workDate,
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.employee-shifts.store'), [
            'assignment_scope' => 'employee',
            'period_mode' => 'single',
            'employee_id' => $this->employee->id,
            'shift_id' => $shiftB->id,
            'work_date' => $workDate,
        ]);

    $response->assertSessionHasErrors('shift_id');
});

test('admin cannot assign shift overlapping evening shift with cross midnight end time', function () {
    $workDate = now()->addDays(3)->toDateString();

    $eveningShift = Shift::create([
        'shift_name' => 'Ca test',
        'start_time' => '20:25:00',
        'end_time' => '21:30:00',
    ]);

    $crossMidnightShift = Shift::create([
        'shift_name' => 'kkik',
        'start_time' => '20:38:00',
        'end_time' => '13:38:00',
    ]);

    EmployeeShift::create([
        'employee_id' => $this->employee->id,
        'shift_id' => $eveningShift->id,
        'work_date' => $workDate,
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.employee-shifts.store'), [
            'assignment_scope' => 'employee',
            'period_mode' => 'single',
            'employee_id' => $this->employee->id,
            'shift_id' => $crossMidnightShift->id,
            'work_date' => $workDate,
        ]);

    $response->assertSessionHasErrors('shift_id');
});
