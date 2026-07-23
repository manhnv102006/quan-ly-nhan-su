<?php

namespace App\Observers;

use App\Mail\CandidateInterviewResultMail;
use App\Models\Candidate;
use App\Models\JobPost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CandidateObserver
{
    public function updated(Candidate $candidate): void
    {
        if ($candidate->wasChanged('status')) {
            $this->syncJobPostQuantityForStatusChange($candidate);
        }

        if (! $candidate->wasChanged('status')) {
            return;
        }

        if (! in_array($candidate->status, ['passed', 'failed'], true)) {
            return;
        }

        DB::afterCommit(function () use ($candidate) {
            $freshCandidate = $candidate->fresh(['jobPost']);

            if (! $freshCandidate || ! filled($freshCandidate->email)) {
                return;
            }

            $mail = new CandidateInterviewResultMail($freshCandidate);

            try {
                Mail::to($freshCandidate->email)
                    ->send($mail);

                $freshCandidate->emailLogs()->create([
                    'email' => $freshCandidate->email,
                    'type' => 'interview_result',
                    'status' => 'sent',
                    'subject' => $mail->subjectText(),
                    'sent_at' => now(),
                ]);
            } catch (Throwable $exception) {
                $freshCandidate->emailLogs()->create([
                    'email' => $freshCandidate->email,
                    'type' => 'interview_result',
                    'status' => 'failed',
                    'subject' => $mail->subjectText(),
                    'error_message' => $exception->getMessage(),
                ]);

                Log::warning('Unable to send interview result email to candidate.', [
                    'candidate_id' => $freshCandidate->id,
                    'candidate_email' => $freshCandidate->email,
                    'candidate_status' => $freshCandidate->status,
                    'error' => $exception->getMessage(),
                ]);
            }
        });
    }

    private function syncJobPostQuantityForStatusChange(Candidate $candidate): void
    {
        $previousStatus = $candidate->getOriginal('status');
        $currentStatus = $candidate->status;

        if ($previousStatus === $currentStatus) {
            return;
        }

        if ($previousStatus === 'passed' && $currentStatus !== 'passed') {
            JobPost::revertSuccessfulHire((int) $candidate->getOriginal('job_post_id'));
        }

        if ($currentStatus === 'passed' && $previousStatus !== 'passed') {
            JobPost::recordSuccessfulHire((int) $candidate->job_post_id);
        }
    }
}
