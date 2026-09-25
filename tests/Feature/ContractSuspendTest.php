<?php

use App\Models\Contract;
use App\Models\ContractSuspension;
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
    ]);

    $this->department = Department::create([
        'department_code' => 'IT',
        'department_name' => 'Phòng IT',
        'max_employees' => 10,
        'status' => 'active',
    ]);

    $this->position = Position::create([
        'position_name' => 'Developer',
        'base_salary' => 10000000,
        'status' => 'active',
    ]);

    $this->employee = Employee::create([
        'employee_code' => 'NV-SUS-001',
        'full_name' => 'Nhân viên tạm hoãn',
        'gender' => 'male',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900000099',
        'email' => 'suspend-employee@example.com',
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'hire_date' => now()->subYear()->toDateString(),
        'status' => 'active',
    ]);

    $this->fixedType = ContractType::create([
        'code' => 'FIXED_1Y',
        'contract_name' => 'HĐ 1 năm',
        'category' => ContractType::CATEGORY_FIXED,
        'duration_month' => 12,
    ]);

    $this->contract = Contract::create([
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'contract_code' => 'HD-SUS-001',
        'start_date' => now()->subMonth()->toDateString(),
        'end_date' => now()->addMonths(6)->toDateString(),
        'salary' => 12000000,
        'status' => Contract::STATUS_ACTIVE,
        'renewal_count' => 0,
    ]);
});

test('admin can suspend an active contract and resume it with the term extended', function () {
    $originalEnd = $this->contract->end_date->toDateString();

    $this->actingAs($this->admin)->post(route('admin.contracts.suspend', $this->contract), [
        'reason' => ContractSuspension::REASON_MUTUAL,
        'start_date' => now()->subDay()->toDateString(),
        'expected_end_date' => now()->addMonth()->toDateString(),
        'note' => 'Thỏa thuận tạm hoãn',
    ])->assertSessionHasErrors('start_date');

    $this->actingAs($this->admin)->post(route('admin.contracts.suspend', $this->contract), [
        'reason' => ContractSuspension::REASON_MUTUAL,
        'start_date' => now()->toDateString(),
        'expected_end_date' => now()->addMonth()->toDateString(),
        'note' => 'Thỏa thuận tạm hoãn',
    ])->assertRedirect(route('admin.contracts.show', $this->contract));

    expect($this->contract->fresh()->status)->toBe(Contract::STATUS_SUSPENDED);

    $this->actingAs($this->admin)->post(route('admin.contracts.store'), [
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'start_date' => now()->addDay()->toDateString(),
        'signed_date' => now()->toDateString(),
        'salary' => '15.000.000',
        'contract_code' => 'HD-SUS-NEW',
        'contract_file' => \Illuminate\Http\UploadedFile::fake()->create('hop-dong.pdf', 20, 'application/pdf'),
    ])->assertSessionHasErrors('employee_id');

    $this->travel(3)->days();

    $this->actingAs($this->admin)->post(route('admin.contracts.resume', $this->contract), [
        'resume_date' => now()->toDateString(),
        'note' => 'Quay lại làm việc',
    ])->assertRedirect(route('admin.contracts.show', $this->contract));

    $resumed = $this->contract->fresh();

    expect($resumed->status)->toBe(Contract::STATUS_ACTIVE)
        ->and($resumed->end_date->toDateString())->toBe(
            \Carbon\Carbon::parse($originalEnd)->addDays(3)->toDateString()
        );
});
