<?php

use App\Mail\CandidateInterviewResultMail;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\JobPost;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->adminRole = Role::create([
        'name' => Role::ADMIN,
        'description' => 'Quản trị viên',
    ]);

    $this->admin = User::factory()->create([
        'role_id' => $this->adminRole->id,
        'status' => 'active',
    ]);

    $this->jobPost = JobPost::create([
        'title' => 'Laravel Developer',
        'quantity' => 1,
        'description' => 'Tuyển lập trình viên Laravel.',
        'status' => 'open',
    ]);

    $this->candidate = Candidate::create([
        'job_post_id' => $this->jobPost->id,
        'full_name' => 'Nguyễn Văn A',
        'phone' => '0900000000',
        'email' => 'candidate@example.com',
        'address' => 'Thành phố Hồ Chí Minh',
        'birth_date' => '1998-01-01',
        'status' => 'interview',
    ]);
});

test('admin updating interview result to passed sends an email to the candidate', function () {
    Mail::fake();

    $interview = Interview::create([
        'candidate_id' => $this->candidate->id,
        'interviewer_id' => null,
        'interview_date' => now()->addDay(),
        'result' => 'pending',
        'note' => null,
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.recruitment.interviews.update', $interview), [
            'result' => 'passed',
            'note' => 'Ứng viên phù hợp với vị trí.',
        ])
        ->assertRedirect(route('admin.recruitment.interviews'));

    expect($interview->fresh()->result)->toBe('passed');
    expect($this->candidate->fresh()->status)->toBe('passed');

    Mail::assertSent(CandidateInterviewResultMail::class, function (CandidateInterviewResultMail $mail) {
        return $mail->hasTo($this->candidate->email)
            && $mail->candidate->is($this->candidate->fresh())
            && $mail->candidate->status === 'passed';
    });
});

test('admin keeping interview result as pending does not send an email', function () {
    Mail::fake();

    $interview = Interview::create([
        'candidate_id' => $this->candidate->id,
        'interviewer_id' => null,
        'interview_date' => now()->addDay(),
        'result' => 'pending',
        'note' => null,
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.recruitment.interviews.update', $interview), [
            'result' => 'pending',
            'note' => 'Chờ phê duyệt thêm.',
        ])
        ->assertRedirect(route('admin.recruitment.interviews'));

    expect($interview->fresh()->result)->toBe('pending');
    expect($this->candidate->fresh()->status)->toBe('interview');

    Mail::assertNothingSent();
});

test('admin updating candidate status to failed sends a rejection email', function () {
    Mail::fake();

    $this->actingAs($this->admin)
        ->put(route('admin.recruitment.candidates.update', $this->candidate), [
            'job_post_id' => $this->jobPost->id,
            'full_name' => $this->candidate->full_name,
            'phone' => $this->candidate->phone,
            'email' => $this->candidate->email,
            'address' => $this->candidate->address,
            'birth_date' => $this->candidate->birth_date?->format('Y-m-d'),
            'status' => 'failed',
        ])
        ->assertRedirect(route('admin.recruitment.candidates.show', $this->candidate));

    expect($this->candidate->fresh()->status)->toBe('failed');

    Mail::assertSent(CandidateInterviewResultMail::class, function (CandidateInterviewResultMail $mail) {
        return $mail->hasTo($this->candidate->email)
            && $mail->candidate->is($this->candidate->fresh())
            && $mail->candidate->status === 'failed';
    });
});
