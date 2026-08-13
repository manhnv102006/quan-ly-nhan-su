<?php

use App\Models\Candidate;
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

    $this->jobPost = JobPost::create([
        'title' => 'Nhan vien hanh chinh',
        'quantity' => 1,
        'status' => 'open',
    ]);
});

test('admin can view candidate list and detail routes', function () {
    $candidate = Candidate::create([
        'job_post_id' => $this->jobPost->id,
        'full_name' => 'Nguyen Van A',
        'phone' => '0900000001',
        'email' => 'candidate-a@example.com',
        'birth_date' => '1998-01-01',
        'address' => 'Ha Noi',
        'status' => Candidate::STATUS_NEW,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.recruitment.candidates'))
        ->assertOk();

    $this->actingAs($this->admin)
        ->get(route('admin.recruitment.candidates.show', $candidate))
        ->assertOk();
});

test('admin cannot manually create candidates anymore', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.recruitment.candidates.store'), [
            'job_post_id' => $this->jobPost->id,
            'full_name' => 'Nguyen Van B',
            'phone' => '0900000002',
            'email' => 'candidate-b@example.com',
            'birth_date' => '1998-01-01',
            'address' => 'Da Nang',
            'status' => 'new',
        ])
        ->assertNotFound();
});
