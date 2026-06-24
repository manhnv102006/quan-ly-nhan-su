<?php

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->adminRole = Role::create(['name' => Role::ADMIN, 'description' => 'Admin']);
    $this->admin = User::factory()->create([
        'role_id' => $this->adminRole->id,
        'status' => 'active',
    ]);

    $this->employee = Employee::create([
        'employee_code' => 'NV-TEST-'.fake()->unique()->numerify('###'),
        'full_name' => 'Nhân viên Test',
        'gender' => 'male',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900000001',
        'email' => 'employee-test@example.com',
        'hire_date' => now()->toDateString(),
        'status' => 'active',
    ]);

    $this->contractType = ContractType::create([
        'contract_name' => 'Hợp đồng lao động',
        'duration_month' => 12,
    ]);
});

test('admin can view contract list', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.contracts.index'))
        ->assertOk();
});

test('admin can create update soft delete restore and force delete contract', function () {
    $createResponse = $this->actingAs($this->admin)->post(route('admin.contracts.store'), [
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->contractType->id,
        'contract_code' => 'HD-TEST-001',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'salary' => 15000000,
        'status' => 'active',
        'signed_date' => '2026-01-01',
        'note' => 'Hợp đồng thử nghiệm',
    ]);

    $createResponse->assertRedirect(route('admin.contracts.index'));

    $contract = Contract::query()->where('contract_code', 'HD-TEST-001')->first();
    expect($contract)->not->toBeNull();

    $this->actingAs($this->admin)
        ->get(route('admin.contracts.show', $contract))
        ->assertOk()
        ->assertSee('HD-TEST-001');

    $this->actingAs($this->admin)
        ->put(route('admin.contracts.update', $contract), [
            'employee_id' => $this->employee->id,
            'contract_type_id' => $this->contractType->id,
            'contract_code' => 'HD-TEST-001',
            'start_date' => '2026-01-01',
            'end_date' => '2027-12-31',
            'salary' => 18000000,
            'status' => 'active',
            'note' => 'Đã cập nhật lương',
        ])
        ->assertRedirect(route('admin.contracts.index'));

    expect($contract->fresh()->salary)->toBe('18000000.00');

    $this->actingAs($this->admin)
        ->delete(route('admin.contracts.destroy', $contract))
        ->assertRedirect(route('admin.contracts.index'));

    expect(Contract::query()->whereKey($contract->id)->exists())->toBeFalse();
    expect(Contract::onlyTrashed()->whereKey($contract->id)->exists())->toBeTrue();

    $this->actingAs($this->admin)
        ->post(route('admin.contracts.restore', $contract->id))
        ->assertRedirect(route('admin.contracts.trashed'));

    expect(Contract::query()->whereKey($contract->id)->exists())->toBeTrue();

    $this->actingAs($this->admin)
        ->delete(route('admin.contracts.destroy', $contract))
        ->assertRedirect(route('admin.contracts.index'));

    $this->actingAs($this->admin)
        ->delete(route('admin.contracts.forceDelete', $contract->id))
        ->assertRedirect(route('admin.contracts.trashed'));

    expect(Contract::withTrashed()->whereKey($contract->id)->exists())->toBeFalse();
});
