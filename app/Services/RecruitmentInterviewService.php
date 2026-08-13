<?php

namespace App\Services;

use App\Mail\CandidateInterviewInvitationMail;
use App\Models\Candidate;
use App\Models\Interview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class RecruitmentInterviewService
{
    public function candidateStatusAfterEvaluation(string $interviewResult): string
    {
        return match ($interviewResult) {
            'passed' => Candidate::STATUS_PENDING_HIRE_APPROVAL,
            'failed' => Candidate::STATUS_FAILED,
            default => Candidate::STATUS_INTERVIEW,
        };
    }

    public function applyEvaluation(Interview $interview, array $validated): void
    {
        $payload = Interview::normalizedEvaluationPayload($validated);

        DB::transaction(function () use ($interview, $payload) {
            $interview->update([
                'status' => $payload['status'],
                'result' => $payload['result'],
                'technical_score' => $payload['technical_score'] ?? null,
                'attitude_score' => $payload['attitude_score'] ?? null,
                'culture_score' => $payload['culture_score'] ?? null,
                'overall_score' => $payload['overall_score'] ?? null,
                'recommendation' => $payload['recommendation'] ?? null,
                'strengths' => $payload['strengths'] ?? null,
                'weaknesses' => $payload['weaknesses'] ?? null,
                'note' => $payload['note'] ?? null,
            ]);

            $candidate = $interview->candidate;

            if ($candidate !== null) {
                $candidate->update([
                    'status' => $this->candidateStatusAfterEvaluation($payload['result']),
                ]);
            }
        });
    }

    /**
     * @return array{candidate_id: int, interviewer_id: ?int, interview_date: string, note: ?string, status: string, result: string}
     */
    public function validateScheduleRequest(Request $request): array
    {
        return $request->validate([
            'candidate_id' => ['required', 'exists:candidates,id', 'unique:interviews,candidate_id'],
            'interview_date' => ['required', 'date', 'after:now'],
            'note' => ['nullable', 'string'],
        ], [
            'candidate_id.required' => 'Ứng viên là bắt buộc.',
            'candidate_id.exists' => 'Ứng viên được chọn không hợp lệ.',
            'candidate_id.unique' => 'Đã tạo lịch phỏng vấn cho ứng viên này rồi.',
            'interview_date.required' => 'Thời gian phỏng vấn là bắt buộc.',
            'interview_date.date' => 'Thời gian phỏng vấn không hợp lệ.',
            'interview_date.after' => 'Thời gian phỏng vấn phải ở tương lai.',
        ]);
    }

    public function scheduleInterview(int $candidateId, string $interviewDate, ?string $note, ?int $interviewerId = null): Interview
    {
        $validated = [
            'candidate_id' => $candidateId,
            'interviewer_id' => $interviewerId ?? $this->resolveInterviewerIdForCandidate($candidateId),
            'interview_date' => $interviewDate,
            'note' => $note,
            'status' => 'scheduled',
            'result' => 'pending',
        ];

        $interview = DB::transaction(function () use ($validated) {
            $candidate = Candidate::query()->findOrFail($validated['candidate_id']);

            $interview = Interview::create($validated);

            $candidate->update([
                'status' => Candidate::STATUS_INTERVIEW,
            ]);

            return $interview;
        });

        DB::afterCommit(function () use ($interview) {
            $this->sendInvitationEmail($interview);
        });

        return $interview;
    }

    public function resolveInterviewerIdForCandidate(int $candidateId): ?int
    {
        return Candidate::query()
            ->whereKey($candidateId)
            ->with('jobPost.department:id,manager_id')
            ->first()
            ?->jobPost
            ?->department
            ?->manager_id;
    }

    private function sendInvitationEmail(Interview $interview): void
    {
        $interview->loadMissing(['candidate.jobPost', 'interviewer']);

        if (! filled($interview->candidate?->email)) {
            return;
        }

        $mail = new CandidateInterviewInvitationMail($interview);

        try {
            Mail::to($interview->candidate->email)->send($mail);

            $interview->candidate->emailLogs()->create([
                'email' => $interview->candidate->email,
                'type' => 'interview_invitation',
                'status' => 'sent',
                'subject' => $mail->subjectText(),
                'sent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $interview->candidate->emailLogs()->create([
                'email' => $interview->candidate->email,
                'type' => 'interview_invitation',
                'status' => 'failed',
                'subject' => $mail->subjectText(),
                'error_message' => $exception->getMessage(),
            ]);

            Log::warning('Unable to send interview invitation email to candidate.', [
                'candidate_id' => $interview->candidate->id,
                'candidate_email' => $interview->candidate->email,
                'interview_id' => $interview->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
