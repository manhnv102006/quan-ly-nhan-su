<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeHistory;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Services\EmployeeHistoryService;

beforeEach(function () {
    $this->adminRole = Role::create(['name' => Role::ADMIN, 'description' => 'Admin']);
    $this->admin = User::factory()->create([
        'role_id' => $this->adminRole->id,
        'status' => 'active',
        'name' => 'Nguyễn Quản Trị',
    ]);

    $this->department = Department::create([
        'department_code' => 'CNTT',
        'department_name' => 'Phòng CNTT',
        'max_employees' => 10,
        'status' => 'active',
    ]);

    $this->position = Position::create([
        'position_name' => 'Developer',
        'description' => null,
        'base_salary' => 10000000,
        'status' => 'active',
    ]);

    $this->employee = Employee::create([
        'employee_code' => 'EMP010',
        'full_name' => 'Phạm Thị Dung',
        'gender' => 'female',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900000010',
        'email' => 'dung-history@example.com',
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'hire_date' => '2024-01-01',
        'status' => 'active',
    ]);

    $this->historyService = app(EmployeeHistoryService::class);
});

test('logs history when creating an employee', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.employees.store'), [
        'full_name' => 'Trần Văn A',
        'gender' => 'male',
        'date_of_birth' => '1990-05-15',
        'phone' => '0912345678',
        'email' => 'tran-a@example.com',
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'hire_date' => '2025-01-01',
        'status' => 'active',
    ]);

    $response->assertRedirect();

    $employee = Employee::query()->where('email', 'tran-a@example.com')->first();
    $history = EmployeeHistory::query()->where('employee_id', $employee->id)->first();

    expect($employee)->not->toBeNull()
        ->and($employee->employee_code)->toBe('CNTT001')
        ->and($history)->not->toBeNull()
        ->and($history->action)->toBe(EmployeeHistory::ACTION_CREATE)
        ->and($history->performed_by)->toBe($this->admin->id)
        ->and($history->summary)->toContain('Nguyễn Quản Trị')
        ->and($history->summary)->toContain('Trần Văn A');
});

test('logs history when updating an employee', function () {
    $response = $this->actingAs($this->admin)->put(route('admin.employees.update', $this->employee), [
        'full_name' => 'Phạm Thị Dung Updated',
        'gender' => 'female',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900000010',
        'email' => 'dung-history@example.com',
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'hire_date' => '2024-01-01',
        'status' => 'inactive',
    ]);

    $response->assertRedirect();

    $history = EmployeeHistory::query()
        ->where('employee_id', $this->employee->id)
        ->where('action', EmployeeHistory::ACTION_UPDATE)
        ->first();

    expect($history)->not->toBeNull()
        ->and($history->changes)->toBeArray()
        ->and(collect($history->changes)->pluck('field'))->toContain('full_name')
        ->and(collect($history->changes)->pluck('field'))->toContain('status');
});

test('logs history when soft deleting an employee', function () {
    $response = $this->actingAs($this->admin)->delete(route('admin.employees.destroy', $this->employee));

    $response->assertRedirect();

    $history = EmployeeHistory::query()
        ->where('employee_id', $this->employee->id)
        ->where('action', EmployeeHistory::ACTION_DELETE)
        ->first();

    expect($history)->not->toBeNull()
        ->and($history->summary)->toContain('xóa mềm')
        ->and($history->summary)->toContain($this->employee->full_name);
});

test('admin can view employee profile history on show page', function () {
    $this->historyService->logCreate($this->employee, $this->admin->id);

    $response = $this->actingAs($this->admin)->get(route('admin.employees.show', $this->employee));

    $response->assertOk()
        ->assertSee('Lịch sử sửa hồ sơ')
        ->assertSee('Nguyễn Quản Trị')
        ->assertSee('Thêm nhân viên');
});
