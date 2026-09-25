<?php

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Services\ContractTypeValidationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
        'employee_code' => 'NV-CT-001',
        'full_name' => 'Nhân viên HĐ',
        'gender' => 'male',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900000001',
        'email' => 'contract-employee@example.com',
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'hire_date' => now()->toDateString(),
        'status' => 'active',
    ]);

    $this->fixedType = ContractType::create([
        'code' => 'FIXED_1Y',
        'contract_name' => 'HĐ 1 năm',
        'category' => ContractType::CATEGORY_FIXED,
        'duration_month' => 12,
    ]);

    $this->probationType = ContractType::create([
        'code' => 'PROBATION',
        'contract_name' => 'Thử việc',
        'category' => ContractType::CATEGORY_PROBATION,
        'duration_month' => 2,
    ]);
});

test('creates first contract with active status and lifecycle defaults', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.contracts.store'), [
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'contract_code' => 'HD-LIFE-001',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addYear()->toDateString(),
        'salary' => 15000000,
    ]);

    $response->assertRedirect(route('admin.contracts.by-employee', $this->employee));

    $contract = Contract::query()->where('contract_code', 'HD-LIFE-001')->first();

    expect($contract)->not->toBeNull()
        ->and($contract->status)->toBe(Contract::STATUS_ACTIVE)
        ->and($contract->previous_contract_id)->toBeNull()
        ->and($contract->renewal_count)->toBe(0)
        ->and($contract->department_id)->toBe($this->department->id)
        ->and($contract->position_id)->toBe($this->position->id)
        ->and($contract->created_by)->toBe($this->admin->id);
});

test('blocks creating new contract when employee already has active contract', function () {
    Contract::create([
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'contract_code' => 'HD-EXISTING',
        'start_date' => now()->subMonth()->toDateString(),
        'end_date' => now()->addYear()->toDateString(),
        'salary' => 12000000,
        'status' => Contract::STATUS_ACTIVE,
        'renewal_count' => 0,
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.contracts.store'), [
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(6)->toDateString(),
        'salary' => 15000000,
    ]);

    $response->assertSessionHasErrors('employee_id');
});

test('sets pending status when start date is in the future', function () {
    $this->actingAs($this->admin)->post(route('admin.contracts.store'), [
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'contract_code' => 'HD-PENDING-001',
        'start_date' => now()->addWeek()->toDateString(),
        'end_date' => now()->addYear()->toDateString(),
        'salary' => 15000000,
    ])->assertRedirect();

    expect(Contract::query()->where('contract_code', 'HD-PENDING-001')->value('status'))
        ->toBe(Contract::STATUS_PENDING);
});

test('contract creation rejects a start date in the past', function () {
    $this->actingAs($this->admin)->post(route('admin.contracts.store'), [
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'start_date' => now()->subDay()->toDateString(),
        'signed_date' => now()->subDay()->toDateString(),
        'salary' => '15.000.000',
        'contract_code' => 'HD-PAST-001',
        'contract_file' => UploadedFile::fake()->create('hop-dong.pdf', 100, 'application/pdf'),
    ])->assertSessionHasErrors('start_date');

    expect(Contract::query()->where('contract_code', 'HD-PAST-001')->exists())->toBeFalse();
});

test('contract creation accepts only a pdf file', function () {
    Storage::fake('public');

    $payload = [
        'employee_id' => $this->employee->id,
        'contract_type_id' => $this->fixedType->id,
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'start_date' => now()->toDateString(),
        'signed_date' => now()->toDateString(),
        'salary' => '15.000.000',
    ];

    $this->actingAs($this->admin)->post(route('admin.contracts.store'), $payload + [
        'contract_code' => 'HD-DOCX-001',
        'contract_file' => UploadedFile::fake()->create(
            'hop-dong.docx',
            100,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ),
    ])->assertSessionHasErrors('contract_file');

    expect(Contract::query()->where('contract_code', 'HD-DOCX-001')->exists())->toBeFalse();

    $this->actingAs($this->admin)->post(route('admin.contracts.store'), $payload + [
        'contract_code' => 'HD-PDF-001',
        'contract_file' => UploadedFile::fake()->create('hop-dong.pdf', 100, 'application/pdf'),
    ])->assertRedirect(route('admin.contracts.by-employee', $this->employee));

    $contract = Contract::query()->where('contract_code', 'HD-PDF-001')->first();

    expect($contract)->not->toBeNull()
        ->and($contract->file_path)->toEndWith('.pdf');

    Storage::disk('public')->assertExists($contract->file_path);
});

test('validates probation contract max duration', function () {
    $validator = app(ContractTypeValidationService::class);

    expect(fn () => $validator->validateAndNormalize(
        $this->probationType,
        '2026-01-01',
        '2026-04-01',
    ))->toThrow(\Illuminate\Validation\ValidationException::class);
});
