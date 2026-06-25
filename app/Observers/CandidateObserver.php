<?php

namespace App\Observers;

use App\Mail\CandidateInterviewResultMail;
use App\Models\Candidate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CandidateObserver
{
    public function updated(Candidate $candidate): void
    {
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

            try {
                Mail::to($freshCandidate->email)
                    ->send(new CandidateInterviewResultMail($freshCandidate));
            } catch (Throwable $exception) {
                Log::warning('Unable to send interview result email to candidate.', [
                    'candidate_id' => $freshCandidate->id,
                    'candidate_email' => $freshCandidate->email,
                    'candidate_status' => $freshCandidate->status,
                    'error' => $exception->getMessage(),
                ]);
            }
        });
    }
}
