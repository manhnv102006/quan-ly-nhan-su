<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Role;
use App\Models\User;
use App\Support\LeaveTypeRegistry;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow('2026-09-21');

    $this->adminRole = Role::create(['name' => Role::ADMIN, 'description' => 'Admin']);
    $this->employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);

    $this->admin = User::factory()->create([
        'role_id' => $this->adminRole->id,
        'status' => 'active',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

function leaveTypePayload(array $overrides = []): array
{
    return array_merge([
        'code' => 'personal_affairs',
        'name' => 'Nghỉ việc riêng',
        'description' => 'Nghỉ giải quyết việc riêng.',
        'annual_deduction' => LeaveType::ANNUAL_DEDUCTION_NO,
        'salary_payer' => LeaveType::PAYER_COMPANY,
        'salary_percent' => '',
        'quota_type' => LeaveType::QUOTA_DAYS_PER_YEAR,
        'quota_days' => '2',
        'quota_note' => '',
        'enforce_quota' => '1',
        'requires_document' => '0',
        'document_hint' => '',
        'gender_restriction' => '',
        'counts_as_leave' => '1',
        'auto_generated' => '0',
        'color' => 'emerald',
        'is_active' => '1',
        'sort_order' => '200',
    ], $overrides);
}

function makeLeaveEmployee(string $gender = 'female'): array
{
    $user = User::factory()->create([
        'role_id' => Role::query()->where('name', Role::EMPLOYEE)->value('id'),
        'status' => 'active',
    ]);

    $employee = Employee::create([
        'user_id' => $user->id,
        'employee_code' => 'NV-LT-'.random_int(1000, 9999),
        'full_name' => 'Nhân viên loại nghỉ',
        'gender' => $gender,
        'date_of_birth' => '1995-01-01',
        'phone' => '0900'.random_int(100000, 999999),
        'email' => 'lt-'.random_int(1000, 9999).'@example.com',
        'hire_date' => '2025-01-01',
        'status' => 'active',
    ]);

    return [$user, $employee];
}

test('default leave type catalog matches the HR policy table', function () {
    expect(LeaveType::query()->count())->toBe(15);

    $annual = LeaveType::query()->where('code', 'annual')->first();
    expect($annual->annual_deduction)->toBe(LeaveType::ANNUAL_DEDUCTION_YES)
        ->and($annual->salary_payer)->toBe(LeaveType::PAYER_COMPANY)
        ->and($annual->quota_type)->toBe(LeaveType::QUOTA_ANNUAL_BALANCE)
        ->and($annual->requiresDocument())->toBeFalse()
        ->and($annual->countsTowardMonthlyPaidQuota())->toBeTrue();

    $holiday = LeaveType::query()->where('code', 'holiday')->first();
    expect($holiday->quota_type)->toBe(LeaveType::QUOTA_DAYS_PER_YEAR)
        ->and($holiday->quota_days)->toBe(11.0)
        ->and($holiday->auto_generated)->toBeTrue()
        ->and($holiday->salary_payer)->toBe(LeaveType::PAYER_COMPANY)
        ->and($holiday->countsTowardMonthlyPaidQuota())->toBeFalse();

    $compensatory = LeaveType::query()->where('code', 'compensatory')->first();
    expect($compensatory->annual_deduction)->toBe(LeaveType::ANNUAL_DEDUCTION_COMPENSATORY)
        ->and($compensatory->quota_type)->toBe(LeaveType::QUOTA_COMPENSATORY_BALANCE)
        ->and($compensatory->salary_payer)->toBe(LeaveType::PAYER_COMPANY);

    $sick = LeaveType::query()->where('code', 'sick')->first();
    expect($sick->salary_payer)->toBe(LeaveType::PAYER_INSURANCE)
        ->and($sick->salary_percent)->toBe(75.0)
        ->and($sick->requiresDocument())->toBeTrue()
        ->and($sick->countsTowardMonthlyPaidQuota())->toBeFalse();

    $wedding = LeaveType::query()->where('code', 'wedding')->first();
    expect($wedding->quota_days)->toBe(3.0)
        ->and($wedding->quota_type)->toBe(LeaveType::QUOTA_DAYS_PER_EVENT)
        ->and($wedding->requiresDocument())->toBeTrue()
        ->and($wedding->salary_payer)->toBe(LeaveType::PAYER_COMPANY)
        ->and($wedding->countsTowardMonthlyPaidQuota())->toBeFalse();

    $childSick = LeaveType::query()->where('code', 'child_sick')->first();
    expect($childSick->salary_payer)->toBe(LeaveType::PAYER_INSURANCE)
        ->and($childSick->requiresDocument())->toBeTrue();

    $prenatal = LeaveType::query()->where('code', 'prenatal_checkup')->first();
    expect($prenatal->quota_type)->toBe(LeaveType::QUOTA_TIMES_PER_PREGNANCY)
        ->and($prenatal->quota_days)->toBe(5.0)
        ->and($prenatal->gender_restriction)->toBe('female')
        ->and($prenatal->salary_payer)->toBe(LeaveType::PAYER_INSURANCE);

    $miscarriage = LeaveType::query()->where('code', 'miscarriage')->first();
    expect($miscarriage->salary_payer)->toBe(LeaveType::PAYER_INSURANCE)
        ->and($miscarriage->gender_restriction)->toBe('female')
        ->and($miscarriage->requiresDocument())->toBeTrue();

    $maternity = LeaveType::query()->where('code', 'maternity')->first();
    expect($maternity->quota_type)->toBe(LeaveType::QUOTA_MONTHS_PER_EVENT)
        ->and($maternity->quota_days)->toBe(6.0)
        ->and($maternity->gender_restriction)->toBe('female');

    $paternity = LeaveType::query()->where('code', 'paternity')->first();
    expect($paternity->gender_restriction)->toBe('male')
        ->and($paternity->salary_payer)->toBe(LeaveType::PAYER_INSURANCE)
        ->and($paternity->requiresDocument())->toBeTrue();

    $unpaid = LeaveType::query()->where('code', 'unpaid')->first();
    expect($unpaid->salary_payer)->toBe(LeaveType::PAYER_NONE)
        ->and($unpaid->quota_type)->toBe(LeaveType::QUOTA_AGREEMENT)
        ->and($unpaid->isPaidLeave())->toBeFalse();

    $businessTrip = LeaveType::query()->where('code', 'business_trip')->first();
    expect($businessTrip->counts_as_leave)->toBeFalse()
        ->and($businessTrip->requiresDocument())->toBeTrue()
        ->and($businessTrip->salary_payer)->toBe(LeaveType::PAYER_COMPANY)
        ->and($businessTrip->countsTowardMonthlyPaidQuota())->toBeFalse();

    $other = LeaveType::query()->where('code', 'other')->first();
    expect($other->annual_deduction)->toBe(LeaveType::ANNUAL_DEDUCTION_APPROVER)
        ->and($other->salary_payer)->toBe(LeaveType::PAYER_APPROVER)
        ->and($other->isPaidLeave())->toBeFalse();
});

test('admin can open the leave type catalog', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.leave-types.index'))
        ->assertOk()
        ->assertSee('Danh mục loại nghỉ phép')
        ->assertSee('Phép năm')
        ->assertSee('Nghỉ vợ sinh con');
});

test('auto generated holiday leave is hidden from the employee form', function () {
    [$user, $employee] = makeLeaveEmployee();

    $this->actingAs($user)
        ->get(route('employee.leave-requests.create'))
        ->assertOk()
        ->assertSee('Phép năm')
        ->assertDontSee('Nghỉ lễ, Tết');

    expect(LeaveRequest::selectableLeaveTypesForEmployee($employee))->not->toContain('holiday');

    $this->actingAs($user)->post(route('employee.leave-requests.store'), [
        'leave_type' => 'holiday',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-05',
        'reason' => 'Nghỉ lễ',
    ])->assertSessionHasErrors('leave_type');
});

test('admin can open the create and edit forms', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.leave-types.create'))
        ->assertOk()
        ->assertSee('Có trừ phép năm không?')
        ->assertSee('Lương do ai trả?')
        ->assertSee('Hạn mức nghỉ')
        ->assertSee('Bắt buộc đính kèm giấy tờ');

    $sick = LeaveType::query()->where('code', 'sick')->firstOrFail();

    $this->actingAs($this->admin)
        ->get(route('admin.leave-types.edit', $sick))
        ->assertOk()
        ->assertSee('Nghỉ ốm')
        ->assertSee('Giấy nghỉ ốm hưởng BHXH hoặc giấy ra viện.');
});

test('employees cannot reach the leave type catalog', function () {
    [$user] = makeLeaveEmployee();

    $this->actingAs($user)
        ->get(route('admin.leave-types.index'))
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('admin.leave-types.store'), leaveTypePayload())
        ->assertRedirect();

    expect(LeaveType::query()->where('code', 'personal_affairs')->exists())->toBeFalse();
});

test('admin can create a leave type and employees can immediately use it', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.leave-types.store'), leaveTypePayload())
        ->assertRedirect(route('admin.leave-types.index'))
        ->assertSessionHasNoErrors();

    $created = LeaveType::query()->where('code', 'personal_affairs')->first();
    expect($created)->not->toBeNull()
        ->and($created->is_system)->toBeFalse()
        ->and($created->quota_days)->toBe(2.0)
        ->and($created->enforce_quota)->toBeTrue();

    [$user, $employee] = makeLeaveEmployee();

    $this->actingAs($user)
        ->get(route('employee.leave-requests.create'))
        ->assertOk()
        ->assertSee('Nghỉ việc riêng');

    $this->actingAs($user)->post(route('employee.leave-requests.store'), [
        'leave_type' => 'personal_affairs',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-06',
        'reason' => 'Giải quyết việc gia đình',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('leave_requests', [
        'employee_id' => $employee->id,
        'leave_type' => 'personal_affairs',
    ]);
});

test('leave type code must be unique and well formed', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.leave-types.store'), leaveTypePayload(['code' => 'annual']))
        ->assertSessionHasErrors('code');

    $this->actingAs($this->admin)
        ->post(route('admin.leave-types.store'), leaveTypePayload(['code' => 'Nghỉ Việc Riêng']))
        ->assertSessionHasErrors('code');
});

test('document hint is required when the type requires an attachment', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.leave-types.store'), leaveTypePayload([
            'requires_document' => '1',
            'document_hint' => '',
        ]))
        ->assertSessionHasErrors('document_hint');
});

test('quota days are required for numeric quota types', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.leave-types.store'), leaveTypePayload(['quota_days' => '']))
        ->assertSessionHasErrors('quota_days');
});

test('admin editing a type changes the document requirement employees face', function () {
    $sick = LeaveType::query()->where('code', 'sick')->firstOrFail();

    $this->actingAs($this->admin)
        ->put(route('admin.leave-types.update', $sick), leaveTypePayload([
            'code' => 'sick',
            'name' => 'Nghỉ ốm',
            'annual_deduction' => LeaveType::ANNUAL_DEDUCTION_NO,
            'salary_payer' => LeaveType::PAYER_INSURANCE,
            'salary_percent' => '75',
            'quota_type' => LeaveType::QUOTA_DAYS_PER_YEAR,
            'quota_days' => '30',
            'enforce_quota' => '0',
            'requires_document' => '0',
            'color' => 'amber',
        ]))
        ->assertRedirect(route('admin.leave-types.index'))
        ->assertSessionHasNoErrors();

    LeaveTypeRegistry::flush();
    expect($sick->fresh()->requiresDocument())->toBeFalse();

    [$user, $employee] = makeLeaveEmployee();

    $this->actingAs($user)->post(route('employee.leave-requests.store'), [
        'leave_type' => 'sick',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-05',
        'reason' => 'Bị cảm',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('leave_requests', [
        'employee_id' => $employee->id,
        'leave_type' => 'sick',
    ]);
});

test('system leave types cannot be renamed by code nor deleted', function () {
    $wedding = LeaveType::query()->where('code', 'wedding')->firstOrFail();

    $this->actingAs($this->admin)->put(route('admin.leave-types.update', $wedding), leaveTypePayload([
        'code' => 'wedding_renamed',
        'name' => 'Nghỉ kết hôn',
        'quota_type' => LeaveType::QUOTA_DAYS_PER_EVENT,
        'quota_days' => '3',
        'requires_document' => '1',
        'document_hint' => 'Giấy đăng ký kết hôn.',
        'color' => 'fuchsia',
    ]))->assertSessionHasNoErrors();

    expect($wedding->fresh()->code)->toBe('wedding');

    $this->actingAs($this->admin)
        ->delete(route('admin.leave-types.destroy', $wedding))
        ->assertRedirect();

    expect(LeaveType::query()->where('code', 'wedding')->exists())->toBeTrue();
});

test('annual and unpaid leave types cannot be switched off', function (string $code) {
    $type = LeaveType::query()->where('code', $code)->firstOrFail();

    $this->actingAs($this->admin)->put(route('admin.leave-types.update', $type), leaveTypePayload([
        'code' => $code,
        'name' => $type->name,
        'quota_type' => $type->quota_type,
        'quota_days' => '',
        'is_active' => '0',
    ]))->assertSessionHasErrors('is_active');

    expect($type->fresh()->is_active)->toBeTrue();
})->with(['annual', 'unpaid']);

test('a custom leave type already used by a request cannot be deleted', function () {
    $this->actingAs($this->admin)->post(route('admin.leave-types.store'), leaveTypePayload());
    $created = LeaveType::query()->where('code', 'personal_affairs')->firstOrFail();

    [, $employee] = makeLeaveEmployee();
    LeaveRequest::create([
        'employee_id' => $employee->id,
        'leave_type' => 'personal_affairs',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-05',
        'total_days' => 1,
        'reason' => 'Việc riêng',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.leave-types.destroy', $created))
        ->assertRedirect();

    expect(LeaveType::query()->whereKey($created->id)->exists())->toBeTrue();
});

test('an unused custom leave type can be deleted', function () {
    $this->actingAs($this->admin)->post(route('admin.leave-types.store'), leaveTypePayload());
    $created = LeaveType::query()->where('code', 'personal_affairs')->firstOrFail();

    $this->actingAs($this->admin)
        ->delete(route('admin.leave-types.destroy', $created))
        ->assertRedirect(route('admin.leave-types.index'));

    expect(LeaveType::query()->whereKey($created->id)->exists())->toBeFalse();
});

test('switching a type off hides it from employees but keeps old requests readable', function () {
    [$user, $employee] = makeLeaveEmployee();

    $request = LeaveRequest::create([
        'employee_id' => $employee->id,
        'leave_type' => 'bereavement',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-05',
        'total_days' => 1,
        'reason' => 'Tang gia',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $bereavement = LeaveType::query()->where('code', 'bereavement')->firstOrFail();
    $bereavement->update(['is_active' => false]);
    LeaveTypeRegistry::flush();

    expect(LeaveRequest::selectableLeaveTypes())->not->toContain('bereavement')
        ->and($request->fresh()->leaveTypeLabel())->toBe('Nghỉ hiếu (tang)');

    $this->actingAs($user)->post(route('employee.leave-requests.store'), [
        'leave_type' => 'bereavement',
        'start_date' => '2026-10-12',
        'end_date' => '2026-10-12',
        'reason' => 'Tang gia',
    ])->assertSessionHasErrors('leave_type');
});
