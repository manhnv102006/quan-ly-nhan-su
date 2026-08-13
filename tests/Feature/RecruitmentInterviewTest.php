<?php

use App\Models\Candidate;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Interview;
use App\Models\JobPost;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $adminRole = Role::create(['name' => Role::ADMIN, 'description' => 'Admin']);
    $managerRole = Role::create(['name' => Role::MANAGER, 'description' => 'Manager']);

    $this->admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'status' => 'active',
    ]);

    $this->managerUser = User::factory()->create([
        'role_id' => $managerRole->id,
        'status' => 'active',
    ]);

    $this->department = Department::create([
        'department_name' => 'Phong IT',
        'status' => 'active',
    ]);

    $this->manager = Employee::create([
        'employee_code' => 'QL001',
        'full_name' => 'Tran Van Manager',
        'gender' => 'male',
        'date_of_birth' => '1985-01-01',
        'phone' => '0900000100',
        'email' => 'manager@example.com',
        'address' => 'Ha Noi',
        'department_id' => $this->department->id,
        'position_id' => null,
        'hire_date' => '2020-01-01',
        'status' => 'active',
    ]);

    $this->department->update(['manager_id' => $this->manager->id]);
    $this->managerUser->update(['employee_id' => $this->manager->id]);

    $this->jobPost = JobPost::create([
        'department_id' => $this->department->id,
        'recruiter_id' => $this->manager->id,
        'title' => 'Lap trinh vien',
        'quantity' => 2,
        'status' => 'open',
    ]);
});

test('manager can create an interview for a department candidate', function () {
    $candidate = Candidate::create([
        'job_post_id' => $this->jobPost->id,
        'full_name' => 'Nguyen Van A',
        'phone' => '0900000001',
        'email' => 'candidate-a@example.com',
        'address' => 'Ha Noi',
        'birth_date' => '1998-01-01',
        'status' => Candidate::STATUS_NEW,
    ]);

    $response = $this->actingAs($this->managerUser)->post(
        route('manager.recruitment.candidates.interviews.store', $candidate),
        [
            'interviewer_id' => $this->manager->id,
            'interview_date' => now()->addDay()->format('Y-m-d H:i:s'),
            'note' => 'Phong van vong 1',
        ]
    );

    $response->assertRedirect(route('manager.recruitment.candidates.show', $candidate));
    $response->assertSessionHas('success');

    expect(Interview::query()->where('candidate_id', $candidate->id)->count())->toBe(1);
    expect($candidate->fresh()->status)->toBe(Candidate::STATUS_INTERVIEW);
});

test('manager scoring passed sends candidate to admin for hire approval', function () {
    $candidate = Candidate::create([
        'job_post_id' => $this->jobPost->id,
        'full_name' => 'Nguyen Van B',
        'phone' => '0900000002',
        'email' => 'candidate-b@example.com',
        'address' => 'Ha Noi',
        'birth_date' => '1998-01-01',
        'status' => Candidate::STATUS_INTERVIEW,
    ]);

    $interview = Interview::create([
        'candidate_id' => $candidate->id,
        'interviewer_id' => $this->manager->id,
        'interview_date' => now()->addDay(),
        'status' => 'scheduled',
        'result' => 'pending',
    ]);

    $this->actingAs($this->managerUser)->put(route('manager.recruitment.interviews.update', $interview), [
        'status' => 'completed',
        'result' => 'passed',
        'overall_score' => 8,
        'technical_score' => 8,
        'attitude_score' => 8,
        'culture_score' => 8,
        'recommendation' => 'hire',
    ]);

    expect($candidate->fresh()->status)->toBe(Candidate::STATUS_PENDING_HIRE_APPROVAL);
    expect($this->jobPost->fresh()->quantity)->toBe(2);
});

test('manager cannot update interview after result has been submitted', function () {
    $candidate = Candidate::create([
        'job_post_id' => $this->jobPost->id,
        'full_name' => 'Nguyen Van E',
        'phone' => '0900000005',
        'email' => 'candidate-e@example.com',
        'address' => 'Ha Noi',
        'birth_date' => '1998-01-01',
        'status' => Candidate::STATUS_PENDING_HIRE_APPROVAL,
    ]);

    $interview = Interview::create([
        'candidate_id' => $candidate->id,
        'interviewer_id' => $this->manager->id,
        'interview_date' => now()->subDay(),
        'status' => 'completed',
        'result' => 'passed',
        'overall_score' => 8,
        'technical_score' => 8,
        'attitude_score' => 8,
        'culture_score' => 8,
        'recommendation' => 'hire',
    ]);

    $response = $this->actingAs($this->managerUser)->put(route('manager.recruitment.interviews.update', $interview), [
        'status' => 'completed',
        'result' => 'failed',
        'overall_score' => 3,
        'technical_score' => 3,
        'attitude_score' => 3,
        'culture_score' => 3,
        'recommendation' => 'reject',
    ]);

    $response->assertRedirect(route('manager.recruitment.index'));
    $response->assertSessionHas('error');

    expect($interview->fresh()->result)->toBe('passed');
    expect($candidate->fresh()->status)->toBe(Candidate::STATUS_PENDING_HIRE_APPROVAL);
});

test('admin approving hire marks candidate passed and decrements job post quantity', function () {
    $candidate = Candidate::create([
        'job_post_id' => $this->jobPost->id,
        'full_name' => 'Nguyen Van C',
        'phone' => '0900000003',
        'email' => 'candidate-c@example.com',
        'address' => 'Ha Noi',
        'birth_date' => '1998-01-01',
        'status' => Candidate::STATUS_PENDING_HIRE_APPROVAL,
    ]);

    Interview::create([
        'candidate_id' => $candidate->id,
        'interviewer_id' => $this->manager->id,
        'interview_date' => now()->subDay(),
        'status' => 'completed',
        'result' => 'passed',
        'overall_score' => 9,
        'technical_score' => 9,
        'attitude_score' => 9,
        'culture_score' => 9,
    ]);

    $this->actingAs($this->admin)->patch(route('admin.recruitment.candidates.approve-hire', $candidate));

    expect($candidate->fresh()->status)->toBe(Candidate::STATUS_PASSED);
    expect($this->jobPost->fresh()->quantity)->toBe(1);
});

test('admin cannot create interview schedule routes anymore', function () {
    $candidate = Candidate::create([
        'full_name' => 'Nguyen Van D',
        'phone' => '0900000004',
        'email' => 'candidate-d@example.com',
        'address' => 'Ha Noi',
        'status' => Candidate::STATUS_NEW,
    ]);

    $this->actingAs($this->admin)->post(route('admin.recruitment.interviews.store'), [
        'candidate_id' => $candidate->id,
        'interview_date' => now()->addDay()->format('Y-m-d H:i:s'),
    ])->assertNotFound();
});
