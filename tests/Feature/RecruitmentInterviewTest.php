<?php

use App\Models\Candidate;
use App\Models\Interview;
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
        'interview_date' => '2026-07-05 09:00:00',
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
        'interview_date' => '2026-07-05 09:00:00',
        'status' => 'scheduled',
        'result' => 'pending',
        'note' => 'Phong van da tao',
    ]);

    $response = $this->actingAs($this->admin)
        ->from(route('admin.recruitment.interviews.create'))
        ->post(route('admin.recruitment.interviews.store'), [
            'candidate_id' => $candidate->id,
            'interviewer_id' => '',
            'interview_date' => '2026-07-06 09:00:00',
            'note' => 'Phong van moi',
        ]);

    $response->assertRedirect(route('admin.recruitment.interviews.create'));
    $response->assertSessionHasErrors([
        'candidate_id' => 'Đã tạo lịch phỏng vấn cho ứng viên này rồi.',
    ]);

    expect(Interview::query()->where('candidate_id', $candidate->id)->count())->toBe(1);
});
