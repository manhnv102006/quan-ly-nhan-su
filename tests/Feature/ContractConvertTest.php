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

    $this->position = Position::create(['position_name' => 'Dev', 'status' => 'active']);

    $this->employee = Employee::create([
        'employee_code' => 'EMP004',
        'full_name' => 'Phạm Thị Dung',
        'gender' => 'female',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900000004',
        'email' => 'dung@example.com',
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'hire_date' => '2024-01-01',
        'status' => 'active',
    ]);

    $this->fixedType = ContractType::create([
        'code' => 'FIXED_1Y',
        'contract_name' => 'Hợp đồng 1 năm',
        'category' => ContractType::CATEGORY_FIXED,
        'duration_month' => 12,
    ]);

    $this->indefiniteType = ContractType::create([
        'code' => 'INDEFINITE',
        'contract_name' => 'Không xác định thời hạn',
        'category' => ContractType::CATEGORY_INDEFINITE,
        'duration_month' => 0,
    ]);

    $this->probationType = ContractType::create([
        'code' => 'PROBATION',
        'contract_name' => 'Thử việc 2 tháng',
        'category' => ContractType::CATEGORY_PROBATION,
        'duration_month' => 2,
    ]);
});

test('converts fixed term with renewal to indefinite', function () {
    $contract = Contract::create([
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'contract_code' => 'HD-11',
        'start_date' => '2025-07-17',
        'end_date' => '2026-07-16',
        'salary' => 12000000,
        'status' => Contract::STATUS_ACTIVE,
        'renewal_count' => 1,
    ]);

    $today = now()->toDateString();

    $this->actingAs($this->admin)->post(route('admin.contracts.convert', $contract), [
        'contract_type_id' => $this->indefiniteType->id,
        'contract_code' => 'HD-12',
        'effective_date' => $today,
        'start_date' => $today,
        'salary' => 15000000,
    ])->assertRedirect();

    $old = $contract->fresh();
    $new = Contract::query()->where('contract_code', 'HD-12')->first();

    expect($old->status)->toBe(Contract::STATUS_REPLACED)
        ->and($old->actual_end_date?->format('Y-m-d'))->toBe($today)
        ->and($new->contract_type_id)->toBe($this->indefiniteType->id)
        ->and($new->previous_contract_id)->toBe($contract->id)
        ->and($new->renewal_count)->toBe(0)
        ->and($new->status)->toBe(Contract::STATUS_ACTIVE)
        ->and($new->end_date)->toBeNull();
});

test('blocks converting fixed to fixed when already renewed', function () {
    $otherFixed = ContractType::create([
        'code' => 'FIXED_3Y',
        'contract_name' => 'Hợp đồng 3 năm',
        'category' => ContractType::CATEGORY_FIXED,
        'duration_month' => 36,
    ]);

    $contract = Contract::create([
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'contract_code' => 'HD-BLOCK',
        'start_date' => '2025-01-01',
        'end_date' => '2026-01-01',
        'salary' => 12000000,
        'status' => Contract::STATUS_ACTIVE,
        'renewal_count' => 1,
    ]);

    $this->actingAs($this->admin)->post(route('admin.contracts.convert', $contract), [
        'contract_type_id' => $otherFixed->id,
        'effective_date' => now()->toDateString(),
        'start_date' => now()->toDateString(),
        'end_date' => now()->addYears(3)->toDateString(),
        'salary' => 15000000,
    ])->assertSessionHasErrors('contract_type_id');
});

test('probation can only convert to fixed or indefinite', function () {
    $contract = Contract::create([
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->probationType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'contract_code' => 'HD-PROB',
        'start_date' => now()->subMonths(2)->toDateString(),
        'end_date' => now()->toDateString(),
        'salary' => 8000000,
        'status' => Contract::STATUS_ACTIVE,
        'renewal_count' => 0,
    ]);

    $this->actingAs($this->admin)->post(route('admin.contracts.convert', $contract), [
        'contract_type_id' => $this->fixedType->id,
        'effective_date' => now()->toDateString(),
        'start_date' => now()->toDateString(),
        'end_date' => now()->addYear()->toDateString(),
        'salary' => 12000000,
    ])->assertRedirect();

    expect(Contract::query()->where('previous_contract_id', $contract->id)->exists())->toBeTrue();
});

test('blocks same contract type as target', function () {
    $contract = Contract::create([
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'contract_code' => 'HD-SAME',
        'start_date' => '2025-01-01',
        'end_date' => '2026-01-01',
        'salary' => 12000000,
        'status' => Contract::STATUS_ACTIVE,
        'renewal_count' => 0,
    ]);

    $this->actingAs($this->admin)->post(route('admin.contracts.convert', $contract), [
        'contract_type_id' => $this->fixedType->id,
        'effective_date' => now()->toDateString(),
        'start_date' => now()->toDateString(),
        'end_date' => now()->addYear()->toDateString(),
        'salary' => 12000000,
    ])->assertSessionHasErrors('contract_type_id');
});
