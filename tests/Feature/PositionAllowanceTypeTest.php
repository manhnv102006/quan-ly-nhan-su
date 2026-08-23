<?php

use App\Models\AllowanceType;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Services\ContractAllowanceService;

beforeEach(function () {
    $this->adminRole = Role::create(['name' => Role::ADMIN, 'description' => 'Admin']);
    $this->admin = User::factory()->create([
        'role_id' => $this->adminRole->id,
        'status' => 'active',
    ]);

    $this->positionType = AllowanceType::create([
        'name' => 'Phụ cấp chức vụ',
        'code' => AllowanceType::CODE_POSITION,
        'default_amount' => 0,
        'calculation_type' => AllowanceType::CALC_PRORATA,
        'calculation_note' => 'Chia theo ngày công thực tế',
        'is_system' => true,
        'is_active' => true,
        'sort_order' => 4,
    ]);

    $this->staff = Position::create([
        'position_name' => 'Nhan vien',
        'base_salary' => 10000000,
        'allowance' => 300000,
        'status' => 'active',
    ]);

    $this->manager = Position::create([
        'position_name' => 'Quan ly',
        'base_salary' => 20000000,
        'allowance' => 800000,
        'status' => 'active',
    ]);
});

test('allowance type index shows per-position label instead of zero default', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.allowance-types.index'))
        ->assertOk()
        ->assertSee('Theo chức vụ')
        ->assertDontSee('0₫');
});

test('position allowance edit form lists each active position amount', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.allowance-types.edit', $this->positionType))
        ->assertOk()
        ->assertSee('Mức theo chức vụ')
        ->assertSee('Nhan vien')
        ->assertSee('Quan ly')
        ->assertSee('name="position_allowances['.$this->staff->id.']"', false)
        ->assertSee('300.000');
});

test('admin can save different position allowance amounts', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.allowance-types.update', $this->positionType), [
            'name' => 'Phụ cấp chức vụ',
            'code' => AllowanceType::CODE_POSITION,
            'calculation_type' => AllowanceType::CALC_PRORATA,
            'calculation_note' => 'Chia theo ngày công thực tế',
            'is_active' => 1,
            'sort_order' => 4,
            'position_allowances' => [
                $this->staff->id => '500.000',
                $this->manager->id => '1.200.000',
            ],
        ])
        ->assertRedirect(route('admin.allowance-types.index'))
        ->assertSessionHas('success');

    expect((float) $this->staff->fresh()->allowance)->toBe(500000.0)
        ->and((float) $this->manager->fresh()->allowance)->toBe(1200000.0)
        ->and((float) $this->positionType->fresh()->default_amount)->toBe(0.0);
});

test('contract allowance service reads amount from selected position', function () {
    $service = app(ContractAllowanceService::class);

    expect($service->positionAllowanceAmount($this->staff->id))->toBe(300000.0)
        ->and($service->positionAllowanceAmount($this->manager->id))->toBe(800000.0)
        ->and($service->positionAllowanceAmount(null))->toBe(0.0)
        ->and($service->positionAllowanceMap()[$this->staff->id])->toBe(300000);
});
