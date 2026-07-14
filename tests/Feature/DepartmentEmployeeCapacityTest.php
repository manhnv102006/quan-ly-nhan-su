<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Rules\DepartmentEmployeeCapacity;
use Illuminate\Support\Facades\Validator;

function createTestDepartment(string $code = 'PB-TEST', int $maxEmployees = 10): Department
{
    return Department::create([
        'department_code' => $code,
        'department_name' => 'Phòng ban test',
        'max_employees' => $maxEmployees,
        'status' => 'active',
    ]);
}

function createTestPosition(): Position
{
    return Position::create([
        'position_name' => 'Nhân viên',
        'description' => null,
        'status' => 'active',
    ]);
}

function createTestEmployee(Department $department, Position $position, int $index = 1): Employee
{
    return Employee::create([
        'employee_code' => 'NV-CAP-'.fake()->unique()->numerify('####'),
        'full_name' => "Nhân viên {$index}",
        'gender' => 'male',
        'date_of_birth' => '1990-01-01',
        'phone' => '09'.fake()->unique()->numerify('########'),
        'email' => fake()->unique()->safeEmail(),
        'department_id' => $department->id,
        'position_id' => $position->id,
        'hire_date' => '2024-01-01',
        'status' => 'active',
    ]);
}

it('rejects assigning employee when department is full', function () {
    $limit = 5;
    $department = createTestDepartment('PB-FULL-1', $limit);
    $position = createTestPosition();

    for ($i = 1; $i <= $limit; $i++) {
        createTestEmployee($department, $position, $i);
    }

    expect($department->fresh()->isAtEmployeeCapacity())->toBeTrue();

    $validator = Validator::make(
        ['department_id' => $department->id],
        ['department_id' => [new DepartmentEmployeeCapacity()]],
    );

    expect($validator->fails())->toBeTrue();
});

it('allows updating employee already in a full department', function () {
    $limit = 4;
    $department = createTestDepartment('PB-FULL-2', $limit);
    $position = createTestPosition();

    $existing = null;

    for ($i = 1; $i <= $limit; $i++) {
        $existing = createTestEmployee($department, $position, $i);
    }

    $validator = Validator::make(
        ['department_id' => $department->id],
        ['department_id' => [new DepartmentEmployeeCapacity($existing->id)]],
    );

    expect($validator->passes())->toBeTrue();
});

it('uses per department max employee limit', function () {
    $department = createTestDepartment('PB-CUSTOM', 15);
    $position = createTestPosition();

    for ($i = 1; $i <= 12; $i++) {
        createTestEmployee($department, $position, $i);
    }

    expect($department->fresh()->remainingEmployeeCapacity())->toBe(3);
    expect($department->maxEmployeesLimit())->toBe(15);
});
