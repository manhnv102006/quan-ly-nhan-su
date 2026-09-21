<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Role;
use App\Models\User;
use App\Support\LeaveTypeRegistry;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Carbon::setTestNow('2026-09-21');
    Storage::fake('public');

    Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
});

afterEach(function () {
    Carbon::setTestNow();
});

function quotaTestEmployee(string $gender = 'female'): array
{
    $user = User::factory()->create([
        'role_id' => Role::query()->where('name', Role::EMPLOYEE)->value('id'),
        'status' => 'active',
    ]);

    $employee = Employee::create([
        'user_id' => $user->id,
        'employee_code' => 'NV-QT-'.random_int(1000, 9999),
        'full_name' => 'Nhân viên hạn mức',
        'gender' => $gender,
        'date_of_birth' => '1995-01-01',
        'phone' => '0900'.random_int(100000, 999999),
        'email' => 'qt-'.random_int(1000, 9999).'@example.com',
        'hire_date' => '2025-01-01',
        'status' => 'active',
    ]);

    return [$user, $employee];
}

test('gender restricted leave types are hidden from the wrong gender', function () {
    [, $female] = quotaTestEmployee('female');
    [, $male] = quotaTestEmployee('male');

    expect(LeaveRequest::selectableLeaveTypesForEmployee($female))
        ->toContain('maternity', 'prenatal_checkup', 'miscarriage')
        ->not->toContain('paternity');

    expect(LeaveRequest::selectableLeaveTypesForEmployee($male))
        ->toContain('paternity')
        ->not->toContain('maternity', 'prenatal_checkup', 'miscarriage');
});

test('a male employee cannot submit a female only leave type', function () {
    [$user, $employee] = quotaTestEmployee('male');

    $this->actingAs($user)->post(route('employee.leave-requests.store'), [
        'leave_type' => 'prenatal_checkup',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-05',
        'reason' => 'Khám thai',
        'supporting_document' => UploadedFile::fake()->create('giay-kham-thai.pdf', 60, 'application/pdf'),
    ])->assertSessionHasErrors('leave_type');

    $this->assertDatabaseMissing('leave_requests', [
        'employee_id' => $employee->id,
        'leave_type' => 'prenatal_checkup',
    ]);
});

test('a male employee can submit paternity leave with the required document', function () {
    [$user, $employee] = quotaTestEmployee('male');

    $this->actingAs($user)->post(route('employee.leave-requests.store'), [
        'leave_type' => 'paternity',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-09',
        'reason' => 'Vợ sinh con',
        'supporting_document' => UploadedFile::fake()->create('giay-chung-sinh.pdf', 60, 'application/pdf'),
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('leave_requests', [
        'employee_id' => $employee->id,
        'leave_type' => 'paternity',
        'total_days' => 5,
    ]);
});

test('paternity leave is rejected without the configured document', function () {
    [$user] = quotaTestEmployee('male');

    $this->actingAs($user)->post(route('employee.leave-requests.store'), [
        'leave_type' => 'paternity',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-09',
        'reason' => 'Vợ sinh con',
    ])->assertSessionHasErrors('supporting_document');
});

test('a per event quota blocks a request longer than the configured limit', function () {
    [$user, $employee] = quotaTestEmployee('female');

    // Kết hôn: 3 ngày/lần, bật chặn. 05/10–09/10 là 5 ngày làm việc.
    $this->actingAs($user)->post(route('employee.leave-requests.store'), [
        'leave_type' => 'wedding',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-09',
        'reason' => 'Kết hôn',
        'supporting_document' => UploadedFile::fake()->create('dkkh.pdf', 60, 'application/pdf'),
    ])->assertSessionHasErrors('leave_type');

    $this->assertDatabaseMissing('leave_requests', [
        'employee_id' => $employee->id,
        'leave_type' => 'wedding',
    ]);

    $this->actingAs($user)->post(route('employee.leave-requests.store'), [
        'leave_type' => 'wedding',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-07',
        'reason' => 'Kết hôn',
        'supporting_document' => UploadedFile::fake()->create('dkkh.pdf', 60, 'application/pdf'),
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('leave_requests', [
        'employee_id' => $employee->id,
        'leave_type' => 'wedding',
        'total_days' => 3,
    ]);
});

test('a yearly quota counts pending requests before blocking a new one', function () {
    LeaveType::query()->where('code', 'child_sick')->update([
        'quota_days' => 2,
        'enforce_quota' => true,
        'requires_document' => false,
    ]);
    LeaveTypeRegistry::flush();

    [$user, $employee] = quotaTestEmployee('female');

    $this->actingAs($user)->post(route('employee.leave-requests.store'), [
        'leave_type' => 'child_sick',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-06',
        'reason' => 'Con ốm',
    ])->assertSessionHasNoErrors();

    $this->actingAs($user)->post(route('employee.leave-requests.store'), [
        'leave_type' => 'child_sick',
        'start_date' => '2026-10-12',
        'end_date' => '2026-10-12',
        'reason' => 'Con ốm lại',
    ])->assertSessionHasErrors('leave_type');

    expect(LeaveRequest::query()->where('employee_id', $employee->id)->count())->toBe(1);
});

test('quota types without enforcement only inform and never block', function () {
    $sick = LeaveType::query()->where('code', 'sick')->firstOrFail();
    expect($sick->quota_days)->toBe(30.0)
        ->and($sick->enforce_quota)->toBeFalse()
        ->and($sick->quotaIsEnforced())->toBeFalse();

    [$user, $employee] = quotaTestEmployee('female');

    // 40 ngày làm việc — vượt con số 30 ngày/năm nhưng không bị chặn vì hạn mức
    // BHXH thay đổi theo thâm niên đóng bảo hiểm của từng người.
    $this->actingAs($user)->post(route('employee.leave-requests.store'), [
        'leave_type' => 'sick',
        'start_date' => '2026-10-05',
        'end_date' => '2026-11-21',
        'reason' => 'Điều trị dài ngày',
        'supporting_document' => UploadedFile::fake()->create('giay-ra-vien.pdf', 60, 'application/pdf'),
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('leave_requests', [
        'employee_id' => $employee->id,
        'leave_type' => 'sick',
    ]);
});

test('only types marked as deducting annual leave touch the annual balance', function () {
    expect(LeaveRequest::annualDeductingLeaveTypes())->toBe(['annual']);

    LeaveType::query()->where('code', 'compensatory')->update([
        'annual_deduction' => LeaveType::ANNUAL_DEDUCTION_YES,
    ]);
    LeaveTypeRegistry::flush();

    expect(LeaveRequest::annualDeductingLeaveTypes())
        ->toContain('annual')
        ->toContain('compensatory');
});

test('paid leave types follow the configured salary payer', function () {
    expect(LeaveRequest::paidLeaveTypes())
        ->toContain('annual', 'sick', 'maternity', 'paternity', 'holiday')
        ->not->toContain('unpaid')
        ->not->toContain('other');

    expect(LeaveRequest::monthlyPaidQuotaLeaveTypes())
        ->toContain('annual', 'half_day')
        ->not->toContain('sick')
        ->not->toContain('wedding')
        ->not->toContain('holiday')
        ->not->toContain('business_trip');

    LeaveType::query()->where('code', 'business_trip')->update([
        'salary_payer' => LeaveType::PAYER_NONE,
    ]);
    LeaveTypeRegistry::flush();

    expect(LeaveRequest::paidLeaveTypes())->not->toContain('business_trip');
});
