<?php

use App\Models\Candidate;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Interview;
use App\Models\JobPost;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $adminRole = Role::create([
        'name' => Role::ADMIN,
        'description' => 'Admin',
    ]);

    $this->admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'status' => 'active',
    ]);
});

test('admin can create an interview for a candidate without an existing interview', function () {
    $candidate = Candidate::create([
        'full_name' => 'Nguyen Van A',
        'phone' => '0900000001',
        'email' => 'candidate-a@example.com',
        'address' => 'Ha Noi',
        'status' => 'new',
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.recruitment.interviews.store'), [
        'candidate_id' => $candidate->id,
        'interviewer_id' => '',
        'interview_date' => now()->addDay()->format('Y-m-d H:i:s'),
        'note' => 'Phong van vong 1',
    ]);

    $response->assertRedirect(route('admin.recruitment.interviews'));
    $response->assertSessionHas('success', 'Tạo lịch phỏng vấn thành công.');

    expect(Interview::query()->where('candidate_id', $candidate->id)->count())->toBe(1);
    expect($candidate->fresh()->status)->toBe('interview');
});

test('admin cannot create a second interview for the same candidate', function () {
    $candidate = Candidate::create([
        'full_name' => 'Nguyen Van B',
        'phone' => '0900000002',
        'email' => 'candidate-b@example.com',
        'address' => 'Da Nang',
        'status' => 'interview',
    ]);

    Interview::create([
        'candidate_id' => $candidate->id,
        'interviewer_id' => null,
        'interview_date' => now()->addDay()->format('Y-m-d H:i:s'),
        'status' => 'scheduled',
        'result' => 'pending',
        'note' => 'Phong van da tao',
    ]);

    $response = $this->actingAs($this->admin)
        ->from(route('admin.recruitment.interviews.create'))
        ->post(route('admin.recruitment.interviews.store'), [
            'candidate_id' => $candidate->id,
            'interviewer_id' => '',
            'interview_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'note' => 'Phong van moi',
        ]);

    $response->assertRedirect(route('admin.recruitment.interviews.create'));
    $response->assertSessionHasErrors([
        'candidate_id' => 'Đã tạo lịch phỏng vấn cho ứng viên này rồi.',
    ]);

    expect(Interview::query()->where('candidate_id', $candidate->id)->count())->toBe(1);
});

test('admin cannot create an interview in the past', function () {
    $candidate = Candidate::create([
        'full_name' => 'Nguyen Van C',
        'phone' => '0900000003',
        'email' => 'candidate-c@example.com',
        'address' => 'Ho Chi Minh',
        'status' => 'new',
    ]);

    $response = $this->actingAs($this->admin)
        ->from(route('admin.recruitment.interviews.create'))
        ->post(route('admin.recruitment.interviews.store'), [
            'candidate_id' => $candidate->id,
            'interviewer_id' => '',
            'interview_date' => now()->subDay()->format('Y-m-d H:i:s'),
            'note' => 'Phong van qua khu',
        ]);

    $response->assertRedirect(route('admin.recruitment.interviews.create'));
    $response->assertSessionHasErrors([
        'interview_date' => 'Thời gian phỏng vấn phải ở tương lai.',
    ]);

    expect(Interview::query()->where('candidate_id', $candidate->id)->count())->toBe(0);
    expect($candidate->fresh()->status)->toBe('new');
});

test('admin can view only passed interview candidates on interviewed candidates page', function () {
    $passedCandidate = Candidate::create([
        'full_name' => 'Nguyen Van Passed Interview',
        'phone' => '0900000011',
        'email' => 'interviewed@example.com',
        'address' => 'Ha Noi',
        'status' => 'passed',
    ]);

    $pendingCandidate = Candidate::create([
        'full_name' => 'Nguyen Van Pending Interview',
        'phone' => '0900000012',
        'email' => 'no-interview@example.com',
        'address' => 'Da Nang',
        'status' => 'interview',
    ]);

    Interview::create([
        'candidate_id' => $passedCandidate->id,
        'interviewer_id' => null,
        'interview_date' => now()->subDay()->format('Y-m-d H:i:s'),
        'status' => 'completed',
        'result' => 'passed',
    ]);

    Interview::create([
        'candidate_id' => $pendingCandidate->id,
        'interviewer_id' => null,
        'interview_date' => now()->subDay()->format('Y-m-d H:i:s'),
        'status' => 'completed',
        'result' => 'pending',
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.recruitment.interviewed-candidates'));

    $response->assertOk();
    $response->assertSee('Nguyen Van Passed Interview');
    $response->assertDontSee('Nguyen Van Pending Interview');
});

test('admin can mark interviewed candidate as passed from interviewed candidates page', function () {
    $candidate = Candidate::create([
        'full_name' => 'Nguyen Van Passed',
        'phone' => '0900000013',
        'email' => 'passed@example.com',
        'address' => 'Ha Noi',
        'status' => 'interview',
    ]);

    $interview = Interview::create([
        'candidate_id' => $candidate->id,
        'interviewer_id' => null,
        'interview_date' => now()->subDay()->format('Y-m-d H:i:s'),
        'status' => 'scheduled',
        'result' => 'pending',
    ]);

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.recruitment.interviewed-candidates.decision', $candidate), [
            'result' => 'passed',
            'note' => 'Dat yeu cau sau phong van',
        ]);

    $response->assertRedirect(route('admin.recruitment.interviewed-candidates'));
    $response->assertSessionHas('success', 'Da cap nhat ket qua phong van thanh cong.');

    expect($candidate->fresh()->status)->toBe('passed');
    expect($interview->fresh()->status)->toBe('completed');
    expect($interview->fresh()->result)->toBe('passed');
    expect($interview->fresh()->note)->toBe('Dat yeu cau sau phong van');
});

test('admin can mark interviewed candidate as failed from interviewed candidates page', function () {
    $candidate = Candidate::create([
        'full_name' => 'Nguyen Van Failed',
        'phone' => '0900000014',
        'email' => 'failed@example.com',
        'address' => 'Ho Chi Minh',
        'status' => 'interview',
    ]);

    $interview = Interview::create([
        'candidate_id' => $candidate->id,
        'interviewer_id' => null,
        'interview_date' => now()->subDay()->format('Y-m-d H:i:s'),
        'status' => 'scheduled',
        'result' => 'pending',
    ]);

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.recruitment.interviewed-candidates.decision', $candidate), [
            'result' => 'failed',
        ]);

    $response->assertRedirect(route('admin.recruitment.interviewed-candidates'));

    expect($candidate->fresh()->status)->toBe('failed');
    expect($interview->fresh()->status)->toBe('completed');
    expect($interview->fresh()->result)->toBe('failed');
});

test('admin can convert passed interview candidate to employee with job department manager', function () {
    $manager = Employee::create([
        'employee_code' => 'MGR001',
        'full_name' => 'Tran Thi Manager',
        'gender' => 'female',
        'date_of_birth' => '1990-01-01',
        'phone' => '0900000020',
        'email' => 'manager@example.com',
        'address' => 'Ha Noi',
        'hire_date' => now()->subYear()->format('Y-m-d'),
        'status' => 'active',
    ]);

    $department = Department::create([
        'manager_id' => $manager->id,
        'department_code' => 'DEV',
        'department_name' => 'Phong Phat Trien',
        'max_employees' => 20,
        'status' => 'active',
    ]);

    $jobPost = JobPost::create([
        'department_id' => $department->id,
        'title' => 'Lap trinh vien PHP',
        'quantity' => 1,
        'status' => 'open',
    ]);

    $candidate = Candidate::create([
        'job_post_id' => $jobPost->id,
        'full_name' => 'Nguyen Van Employee',
        'phone' => '0900000015',
        'email' => 'employee-candidate@example.com',
        'address' => 'Ho Chi Minh',
        'birth_date' => '1998-02-03',
        'status' => 'passed',
    ]);

    Interview::create([
        'candidate_id' => $candidate->id,
        'interviewer_id' => null,
        'interview_date' => now()->subDay()->format('Y-m-d H:i:s'),
        'status' => 'completed',
        'result' => 'passed',
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.recruitment.candidates.convert-to-employee', $candidate), [
            'employee_code' => 'NV-PASS-001',
            'gender' => 'male',
            'date_of_birth' => '1998-02-03',
            'department_id' => $department->id,
            'position_id' => '',
            'hire_date' => now()->format('Y-m-d'),
            'status' => 'active',
        ]);

    $employee = Employee::where('employee_code', 'NV-PASS-001')->first();

    $response->assertRedirect(route('admin.employees.show', $employee));
    expect($employee)->not->toBeNull();
    expect($employee->department_id)->toBe($department->id);
    expect($employee->manager_id)->toBe($manager->id);
    expect($candidate->fresh()->employee_id)->toBe($employee->id);
});
