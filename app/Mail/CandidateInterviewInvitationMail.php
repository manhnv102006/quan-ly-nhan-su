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
}
