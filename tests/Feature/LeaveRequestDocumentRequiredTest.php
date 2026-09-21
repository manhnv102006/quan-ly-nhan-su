<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDocument;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Carbon::setTestNow('2026-09-21');
    Storage::fake('public');

    $this->employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $this->user = User::factory()->create([
        'role_id' => $this->employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee = Employee::create([
        'user_id' => $this->user->id,
        'employee_code' => 'NV-B9-'.random_int(100, 999),
        'full_name' => 'Nhân viên B9',
        'gender' => 'female',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900'.random_int(100000, 999999),
        'email' => 'b9-'.random_int(1000, 9999).'@example.com',
        'hire_date' => '2025-01-01',
        'status' => 'active',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

function documentRequiredPayload(array $overrides = []): array
{
    return array_merge([
        'leave_type' => 'sick',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-06',
        'reason' => 'Bị cảm cúm',
    ], $overrides);
}

test('B9: sick leave without supporting document is blocked', function () {
    $response = $this->actingAs($this->user)->post(route('employee.leave-requests.store'), documentRequiredPayload());

    $response->assertSessionHasErrors('supporting_document');
    $this->assertDatabaseMissing('leave_requests', [
        'employee_id' => $this->employee->id,
        'leave_type' => 'sick',
    ]);
});

test('B9: sick leave with supporting document is accepted', function () {
    $response = $this->actingAs($this->user)->post(route('employee.leave-requests.store'), [
        ...documentRequiredPayload(),
        'supporting_document' => UploadedFile::fake()->create('giay-bac-si.pdf', 120, 'application/pdf'),
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.leave-requests'));

    $leaveRequest = LeaveRequest::query()->where('employee_id', $this->employee->id)->first();
    expect($leaveRequest)->not->toBeNull()
        ->and($leaveRequest->leave_type)->toBe('sick');
    expect(LeaveRequestDocument::query()->where('leave_request_id', $leaveRequest->id)->exists())->toBeTrue();
});

test('B9: annual leave does not require supporting document', function () {
    $response = $this->actingAs($this->user)->post(route('employee.leave-requests.store'), documentRequiredPayload([
        'leave_type' => 'annual',
    ]));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.leave-requests'));
    expect(LeaveRequestDocument::query()->count())->toBe(0);
});

test('B9: all document-required leave types reject missing attachment', function (string $leaveType) {
    if ($leaveType === 'maternity' && $this->employee->gender !== 'female') {
        $this->markTestSkipped('Maternity only for female employees.');
    }

    $response = $this->actingAs($this->user)->post(
        route('employee.leave-requests.store'),
        documentRequiredPayload(['leave_type' => $leaveType]),
    );

    $response->assertSessionHasErrors('supporting_document');
})->with(['sick', 'maternity', 'child_sick', 'wedding', 'bereavement']);

test('B9: employee can download attached document', function () {
    $leaveRequest = LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'leave_type' => 'sick',
        'start_date' => '2026-10-06',
        'end_date' => '2026-10-06',
        'total_days' => 1,
        'reason' => 'Ốm',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    $path = 'leave-requests/'.$this->employee->id.'/'.$leaveRequest->id.'/giay.pdf';
    Storage::disk('public')->put($path, 'pdf-content');

    LeaveRequestDocument::create([
        'leave_request_id' => $leaveRequest->id,
        'file_path' => $path,
        'original_name' => 'giay-bac-si.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 11,
    ]);

    $response = $this->actingAs($this->user)->get(route('employee.leave-requests.document', $leaveRequest));

    $response->assertOk();
});
