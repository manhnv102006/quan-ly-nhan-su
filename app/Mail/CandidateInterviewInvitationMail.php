<?php

namespace App\Mail;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CandidateInterviewInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Interview $interview)
    {
    }
}
