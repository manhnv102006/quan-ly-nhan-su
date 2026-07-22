<?php

use App\Models\Candidate;
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

test('interview result passed decrements job post quantity and closes when zero', function () {
    $jobPost = JobPost::create([
        'title' => 'Lap trinh vien',
        'quantity' => 2,
        'status' => 'open',
    ]);

    $candidate = Candidate::create([
        'job_post_id' => $jobPost->id,
        'full_name' => 'Ung vien dat',
        'phone' => '0900000099',
        'email' => 'passed-hire@example.com',
        'address' => 'Ha Noi',
        'birth_date' => '1995-05-05',
        'status' => 'interview',
    ]);

    $interview = Interview::create([
        'candidate_id' => $candidate->id,
        'interviewer_id' => null,
        'interview_date' => now()->addDay(),
        'status' => 'scheduled',
        'result' => 'pending',
    ]);

    $this->actingAs($this->admin)->put(route('admin.recruitment.interviews.update', $interview), [
        'status' => 'completed',
        'result' => 'passed',
    ]);

    expect($jobPost->fresh()->quantity)->toBe(1);
    expect($jobPost->fresh()->status)->toBe('open');

    $candidateTwo = Candidate::create([
        'job_post_id' => $jobPost->id,
        'full_name' => 'Ung vien dat 2',
        'phone' => '0900000098',
        'email' => 'passed-hire-2@example.com',
        'address' => 'Ha Noi',
        'birth_date' => '1996-06-06',
        'status' => 'interview',
    ]);

    $interviewTwo = Interview::create([
        'candidate_id' => $candidateTwo->id,
        'interviewer_id' => null,
        'interview_date' => now()->addDays(2),
        'status' => 'scheduled',
        'result' => 'pending',
    ]);

    $this->actingAs($this->admin)->put(route('admin.recruitment.interviews.update', $interviewTwo), [
        'status' => 'completed',
        'result' => 'passed',
    ]);

    expect($jobPost->fresh()->quantity)->toBe(0);
    expect($jobPost->fresh()->status)->toBe('closed');
});
