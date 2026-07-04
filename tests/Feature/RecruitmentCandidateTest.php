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

test('admin can create a candidate without cv when required fields are complete', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.recruitment.candidates.store'), [
        'job_post_id' => $this->jobPost->id,
        'full_name' => 'Nguyen Van A',
        'phone' => '0900000001',
        'email' => 'candidate-a@example.com',
        'birth_date' => '1998-01-01',
        'address' => 'Ha Noi',
        'status' => 'new',
    ]);

    $response->assertRedirect(route('admin.recruitment.candidates'));

    $candidate = Candidate::query()->where('email', 'candidate-a@example.com')->first();

    expect($candidate)->not->toBeNull();
    expect($candidate->phone)->toBe('0900000001');
    expect($candidate->cv_file)->toBeNull();
});

test('admin cannot create a candidate with invalid phone number', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.recruitment.candidates.create'))
        ->post(route('admin.recruitment.candidates.store'), [
            'job_post_id' => $this->jobPost->id,
            'full_name' => 'Nguyen Van B',
            'phone' => '090000001',
            'email' => 'candidate-b@example.com',
            'birth_date' => '1998-01-01',
            'address' => 'Da Nang',
            'status' => 'new',
        ]);

    $response->assertRedirect(route('admin.recruitment.candidates.create'));
    $response->assertSessionHasErrors([
        'phone' => 'Số điện thoại phải gồm đúng 10 chữ số.',
    ]);

    expect(Candidate::query()->where('email', 'candidate-b@example.com')->exists())->toBeFalse();
});

test('admin cannot create a candidate with duplicate email or phone', function () {
    Candidate::create([
        'job_post_id' => $this->jobPost->id,
        'full_name' => 'Nguyen Van C',
        'phone' => '0900000003',
        'email' => 'candidate-c@example.com',
        'birth_date' => '1998-01-01',
        'address' => 'Can Tho',
        'status' => 'new',
    ]);

    $response = $this->actingAs($this->admin)
        ->from(route('admin.recruitment.candidates.create'))
        ->post(route('admin.recruitment.candidates.store'), [
            'job_post_id' => $this->jobPost->id,
            'full_name' => 'Nguyen Van D',
            'phone' => '0900000003',
            'email' => 'candidate-c@example.com',
            'birth_date' => '1998-01-01',
            'address' => 'Hue',
            'status' => 'new',
        ]);

    $response->assertRedirect(route('admin.recruitment.candidates.create'));
    $response->assertSessionHasErrors([
        'phone' => 'Số điện thoại này đã tồn tại trong danh sách ứng viên.',
        'email' => 'Email này đã tồn tại trong danh sách ứng viên.',
    ]);

    expect(Candidate::query()->where('email', 'candidate-c@example.com')->count())->toBe(1);
});
