<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
});

function createEmployeeWithGender(string $gender): array
{
    $user = User::factory()->create([
        'role_id' => Role::where('name', Role::EMPLOYEE)->value('id'),
        'status' => 'active',
    ]);

    $employee = Employee::create([
        'user_id' => $user->id,
        'employee_code' => 'NV-MAT-'.strtoupper(substr($gender, 0, 1)).'-'.random_int(100, 999),
        'full_name' => 'Nhân viên '.ucfirst($gender),
        'gender' => $gender,
        'date_of_birth' => '1995-01-01',
        'phone' => '0900'.random_int(100000, 999999),
        'email' => 'mat-'.$gender.'-'.random_int(1000, 9999).'@example.com',
        'hire_date' => now()->toDateString(),
        'status' => 'active',
    ]);

    return [$user, $employee];
}

test('male employees cannot select maternity leave type', function () {
    [, $employee] = createEmployeeWithGender('male');

    expect(LeaveRequest::selectableLeaveTypesForEmployee($employee))
        ->not->toContain('maternity')
        ->and(LeaveRequest::leaveTypeLabelsForEmployee($employee))
        ->not->toHaveKey('maternity');
});

test('female employees can select maternity leave type', function () {
    [, $employee] = createEmployeeWithGender('female');

    expect(LeaveRequest::selectableLeaveTypesForEmployee($employee))
        ->toContain('maternity')
        ->and(LeaveRequest::leaveTypeLabelsForEmployee($employee))
        ->toHaveKey('maternity');
});

test('male employee cannot submit maternity leave request', function () {
    [$user, $employee] = createEmployeeWithGender('male');

    $response = $this->actingAs($user)->post(route('employee.leave-requests.store'), [
        'leave_type' => 'maternity',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-10',
        'reason' => 'Thai sản',
    ]);

    $response->assertSessionHasErrors('leave_type');
    $this->assertDatabaseMissing('leave_requests', [
        'employee_id' => $employee->id,
        'leave_type' => 'maternity',
    ]);
});

test('female employee can submit maternity leave request', function () {
    [$user, $employee] = createEmployeeWithGender('female');

    $response = $this->actingAs($user)->post(route('employee.leave-requests.store'), [
        'leave_type' => 'maternity',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-10',
        'reason' => 'Nghỉ thai sản',
        'supporting_document' => UploadedFile::fake()->create('giay-thai-san.pdf', 100, 'application/pdf'),
    ]);

    $response->assertRedirect(route('employee.leave-requests'));
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('leave_requests', [
        'employee_id' => $employee->id,
        'leave_type' => 'maternity',
    ]);
});
