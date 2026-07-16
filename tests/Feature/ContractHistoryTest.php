<?php

use App\Models\Contract;
use App\Models\ContractHistory;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Services\ContractService;

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
        'employee_code' => 'EMP010',
        'full_name' => 'Phạm Thị Dung',
        'gender' => 'female',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900000010',
        'email' => 'dung-history@example.com',
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

    $this->service = app(ContractService::class);
});

test('logs history when creating a contract', function () {
    $contract = $this->service->create([
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-12-31',
        'salary' => 12000000,
    ], $this->admin->id);

    $history = ContractHistory::query()->where('contract_id', $contract->id)->first();

    expect($history)->not->toBeNull()
        ->and($history->action)->toBe(ContractHistory::ACTION_CREATE)
        ->and($history->employee_id)->toBe($this->employee->id)
        ->and($history->performed_by)->toBe($this->admin->id)
        ->and($history->summary)->toContain('Nguyễn Quản Trị')
        ->and($history->summary)->toContain($this->employee->full_name);
});

test('logs history when updating a contract', function () {
    $contract = Contract::create([
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'contract_code' => 'HD-TEST-001',
        'start_date' => '2025-01-01',
        'end_date' => '2025-12-31',
        'salary' => 12000000,
        'status' => Contract::STATUS_DRAFT,
    ]);

    $this->service->update($contract, [
        'salary' => 15000000,
        'description' => 'Cập nhật lương',
    ], $this->admin->id);

    $history = ContractHistory::query()
        ->where('contract_id', $contract->id)
        ->where('action', ContractHistory::ACTION_UPDATE)
        ->first();

    expect($history)->not->toBeNull()
        ->and($history->changes)->toBeArray()
        ->and(collect($history->changes)->pluck('field'))->toContain('salary');
});

test('logs history when cancelling a contract', function () {
    $contract = Contract::create([
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'contract_code' => 'HD-TEST-002',
        'start_date' => '2025-01-01',
        'end_date' => '2025-12-31',
        'salary' => 12000000,
        'status' => Contract::STATUS_ACTIVE,
    ]);

    $this->service->cancel($contract, [
        'end_date' => '2025-06-01',
        'note' => 'Hủy theo yêu cầu',
    ], $this->admin->id);

    $history = ContractHistory::query()
        ->where('contract_id', $contract->id)
        ->where('action', ContractHistory::ACTION_CANCEL)
        ->first();

    expect($history)->not->toBeNull()
        ->and($history->note)->toBe('Hủy theo yêu cầu')
        ->and($history->summary)->toContain('hủy hợp đồng');
});

test('admin can view contract history page', function () {
    $contract = $this->service->create([
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-12-31',
        'salary' => 12000000,
    ], $this->admin->id);

    $response = $this->actingAs($this->admin)->get(route('admin.contracts.history', [
        'employee_id' => $this->employee->id,
    ]));

    $response->assertOk()
        ->assertSee('Lịch sử hợp đồng')
        ->assertSee($contract->contract_code)
        ->assertSee('Nguyễn Quản Trị');
});
