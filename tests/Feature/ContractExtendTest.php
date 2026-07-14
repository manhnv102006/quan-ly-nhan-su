<?php

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;

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
        'status' => 'active',
    ]);

    $this->employee = Employee::create([
        'employee_code' => 'EMP004',
        'full_name' => 'Phạm Thị Dung',
        'gender' => 'female',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900000004',
        'email' => 'dung@example.com',
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'user_id' => User::factory()->create(['role_id' => $this->adminRole->id])->id,
        'hire_date' => '2024-01-01',
        'status' => 'active',
    ]);

    $this->fixedType = ContractType::create([
        'code' => 'FIXED_1Y',
        'contract_name' => 'Hợp đồng 1 năm',
        'category' => ContractType::CATEGORY_FIXED,
        'duration_month' => 12,
    ]);
});

test('blocks extend when fixed term contract already renewed once', function () {
    $contract = Contract::create([
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'contract_code' => 'HD-OLD-001',
        'start_date' => '2024-07-17',
        'end_date' => '2025-07-16',
        'salary' => 12000000,
        'status' => Contract::STATUS_ACTIVE,
        'renewal_count' => 1,
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.contracts.extend', $contract), [
        'contract_type_id' => $this->fixedType->id,
        'start_date' => '2025-07-17',
        'end_date' => '2026-07-16',
        'salary' => 13000000,
    ]);

    $response->assertRedirect(route('admin.contracts.show', $contract));
    expect($contract->fresh()->status)->toBe(Contract::STATUS_ACTIVE);
});

test('extends active contract and replaces old one', function () {
    $contract = Contract::create([
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'contract_code' => 'HD-EXT-OLD',
        'start_date' => '2025-07-17',
        'end_date' => '2026-07-16',
        'salary' => 12000000,
        'status' => Contract::STATUS_ACTIVE,
        'renewal_count' => 0,
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.contracts.extend', $contract), [
        'contract_type_id' => $this->fixedType->id,
        'contract_code' => 'HD-EXT-NEW',
        'start_date' => '2026-07-17',
        'end_date' => '2027-07-16',
        'salary' => 15000000,
    ]);

    $response->assertRedirect();

    $old = $contract->fresh();
    $new = Contract::query()->where('contract_code', 'HD-EXT-NEW')->first();

    expect($old->status)->toBe(Contract::STATUS_REPLACED)
        ->and($old->actual_end_date?->format('Y-m-d'))->toBe('2026-07-16')
        ->and($new)->not->toBeNull()
        ->and($new->previous_contract_id)->toBe($contract->id)
        ->and($new->renewal_count)->toBe(1)
        ->and($new->status)->toBe(Contract::STATUS_PENDING);
});
