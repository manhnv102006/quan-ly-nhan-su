<?php

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
