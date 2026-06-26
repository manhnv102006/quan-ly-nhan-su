<?php

namespace App\Mail;

use App\Models\Candidate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidateInterviewResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Candidate $candidate)
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
        return $this->candidate->status === 'passed'
            ? 'Chúc mừng bạn đã vượt qua vòng phỏng vấn'
            : 'Thông báo kết quả phỏng vấn';
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.candidates.interview-result',
            with: [
                'candidate' => $this->candidate,
                'isPassed' => $this->candidate->status === 'passed',
                'jobTitle' => $this->candidate->jobPost?->title,
            ],
        );
    }
}
