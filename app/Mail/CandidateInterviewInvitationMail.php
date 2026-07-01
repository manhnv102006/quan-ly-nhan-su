<?php

namespace App\Mail;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidateInterviewInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Interview $interview)
    {
        $this->interview->loadMissing(['candidate.jobPost', 'interviewer']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectText()
        );
    }

    public function subjectText(): string
    {
        return 'Thư mời phỏng vấn tại '.config('app.name');
    }

    public function interviewTimeText(): string
    {
        return $this->interview->interview_date?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '-';
    }
}
