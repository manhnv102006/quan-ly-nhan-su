<?php

use App\Models\Candidate;
use App\Models\JobPost;

test('guests only see open and unexpired job posts on public recruitment page', function () {
    $visibleJob = JobPost::create([
        'title' => 'Public PHP Developer',
        'quantity' => 2,
        'application_deadline' => now()->addWeek()->toDateString(),
        'status' => 'open',
    ]);

    $noDeadlineJob = JobPost::create([
        'title' => 'Public HR Specialist',
        'quantity' => 1,
        'application_deadline' => null,
        'status' => 'open',
    ]);

    $closedJob = JobPost::create([
        'title' => 'Closed Accountant',
        'quantity' => 1,
        'application_deadline' => now()->addWeek()->toDateString(),
        'status' => 'closed',
    ]);

    $expiredJob = JobPost::create([
        'title' => 'Expired Designer',
        'quantity' => 1,
        'application_deadline' => now()->subDay()->toDateString(),
        'status' => 'open',
    ]);

    $response = $this->get(route('public.recruitment.index'));

    $response->assertOk();
    $response->assertSee($visibleJob->title);
    $response->assertSee($noDeadlineJob->title);
    $response->assertDontSee($closedJob->title);
    $response->assertDontSee($expiredJob->title);
});

test('guests can apply to a public job without uploading a cv', function () {
    $jobPost = JobPost::create([
        'title' => 'Public Laravel Developer',
        'quantity' => 2,
        'application_deadline' => now()->addWeek()->toDateString(),
        'status' => 'open',
    ]);

    $response = $this->post(route('public.recruitment.apply.store', $jobPost), [
        'full_name' => 'Nguyen Van Public',
        'phone' => '0912345678',
        'email' => 'public-candidate@example.com',
        'birth_date' => '1998-05-10',
        'address' => 'Ha Noi',
    ]);

    $response->assertRedirect(route('public.recruitment.show', $jobPost));
    $response->assertSessionHas('application_success');

    $candidate = Candidate::query()->where('email', 'public-candidate@example.com')->first();

    expect($candidate)->not->toBeNull();
    expect($candidate->job_post_id)->toBe($jobPost->id);
    expect($candidate->status)->toBe('new');
    expect($candidate->cv_file)->toBeNull();
});

test('guests cannot view or apply to closed and expired public jobs', function () {
    $closedJob = JobPost::create([
        'title' => 'Closed Public Role',
        'quantity' => 1,
        'application_deadline' => now()->addWeek()->toDateString(),
        'status' => 'closed',
    ]);

    $expiredJob = JobPost::create([
        'title' => 'Expired Public Role',
        'quantity' => 1,
        'application_deadline' => now()->subDay()->toDateString(),
        'status' => 'open',
    ]);

    $this->get(route('public.recruitment.show', $closedJob))->assertNotFound();
    $this->get(route('public.recruitment.apply', $closedJob))->assertNotFound();
    $this->post(route('public.recruitment.apply.store', $closedJob), [])->assertNotFound();

    $this->get(route('public.recruitment.show', $expiredJob))->assertNotFound();
    $this->get(route('public.recruitment.apply', $expiredJob))->assertNotFound();
    $this->post(route('public.recruitment.apply.store', $expiredJob), [])->assertNotFound();
});

test('public application rejects duplicate phone and email', function () {
    $jobPost = JobPost::create([
        'title' => 'Public Sales Executive',
        'quantity' => 1,
        'application_deadline' => now()->addWeek()->toDateString(),
        'status' => 'open',
    ]);

    Candidate::create([
        'job_post_id' => $jobPost->id,
        'full_name' => 'Existing Candidate',
        'phone' => '0901111222',
        'email' => 'existing-public@example.com',
        'address' => 'Da Nang',
        'birth_date' => '1997-01-01',
        'status' => 'new',
    ]);

    $response = $this
        ->from(route('public.recruitment.apply', $jobPost))
        ->post(route('public.recruitment.apply.store', $jobPost), [
            'full_name' => 'Duplicate Candidate',
            'phone' => '0901111222',
            'email' => 'existing-public@example.com',
            'birth_date' => '1998-05-10',
            'address' => 'Ha Noi',
        ]);

    $response->assertRedirect(route('public.recruitment.apply', $jobPost));
    $response->assertSessionHasErrors(['phone', 'email']);

    expect(Candidate::query()->where('email', 'existing-public@example.com')->count())->toBe(1);
});
