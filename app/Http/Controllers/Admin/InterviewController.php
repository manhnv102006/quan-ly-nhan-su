<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CandidateInterviewInvitationMail;
use App\Models\Candidate;
use App\Models\Interview;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\UpdateInterviewEvaluationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class InterviewController extends Controller
{
    public function create(): View
    {
        $candidates = Candidate::query()
            ->with(['jobPost.department.manager'])
            ->orderBy('full_name')
            ->get(['id', 'job_post_id', 'full_name', 'status']);

        return view('admin.recruitment.interviews.create', compact('candidates'));
    }

    public function index(): View
    {
        $interviews = Interview::query()
            ->with(['candidate.jobPost.department', 'interviewer'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Interview::count(),
            'pending' => Interview::where('result', 'pending')->count(),
            'passed' => Interview::where('result', 'passed')->count(),
            'failed' => Interview::where('result', 'failed')->count(),
            'scheduled' => Interview::where('status', 'scheduled')->count(),
            'completed' => Interview::where('status', 'completed')->count(),
            'cancelled' => Interview::where('status', 'cancelled')->count(),
            'no_show' => Interview::where('status', 'no_show')->count(),
        ];

        return view('admin.recruitment.interviews.index', compact('interviews', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
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

        $validated['interviewer_id'] = $this->resolveInterviewerIdForCandidate((int) $validated['candidate_id']);
        $validated['status'] = 'scheduled';
        $validated['result'] = 'pending';

        $interview = DB::transaction(function () use ($validated) {
            $candidate = Candidate::query()->findOrFail($validated['candidate_id']);

            $interview = Interview::create($validated);

            $candidate->update([
                'status' => 'interview',
            ]);

            return $interview;
        });

        DB::afterCommit(function () use ($interview) {
            $interview->loadMissing(['candidate.jobPost', 'interviewer']);

            if (filled($interview->candidate?->email)) {
                $mail = new CandidateInterviewInvitationMail($interview);

                try {
                    Mail::to($interview->candidate->email)
                        ->send($mail);

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
        });

        $returnTo = $request->input('return_to');
        if (is_string($returnTo) && $this->isCandidateShowReturnUrl($returnTo, (int) $validated['candidate_id'])) {
            return redirect()
                ->route('admin.recruitment.interviews')
                ->with('success', 'Tạo lịch phỏng vấn thành công. Lịch đã hiển thị trong danh sách bên dưới.');
        }

        return redirect()
            ->route('admin.recruitment.interviews')
            ->with('success', 'Tạo lịch phỏng vấn thành công.');
    }

    private function isCandidateShowReturnUrl(string $url, int $candidateId): bool
    {
        $expected = route('admin.recruitment.candidates.show', $candidateId, absolute: false);

        return str_starts_with(parse_url($url, PHP_URL_PATH) ?? '', $expected);
    }

    private function resolveInterviewerIdForCandidate(int $candidateId): ?int
    {
        $managerId = Candidate::query()
            ->whereKey($candidateId)
            ->with('jobPost.department:id,manager_id')
            ->first()
            ?->jobPost
            ?->department
            ?->manager_id;

        return $managerId ?: null;
    }

    public function update(UpdateInterviewEvaluationRequest $request, Interview $interview): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($interview, $validated) {
            $interview->update([
                'status' => $validated['status'],
                'result' => $validated['result'],
                'technical_score' => $validated['technical_score'] ?? null,
                'attitude_score' => $validated['attitude_score'] ?? null,
                'culture_score' => $validated['culture_score'] ?? null,
                'overall_score' => $validated['overall_score'] ?? null,
                'recommendation' => $validated['recommendation'] ?? null,
                'strengths' => $validated['strengths'] ?? null,
                'weaknesses' => $validated['weaknesses'] ?? null,
                'note' => $validated['note'] ?? null,
            ]);

            $candidateStatus = match ($validated['result']) {
                'passed' => 'passed',
                'failed' => 'failed',
                default => 'interview',
            };

            $candidate = $interview->candidate;

            if ($candidate !== null) {
                $candidate->update([
                    'status' => $candidateStatus,
                ]);
            }
        });

        return redirect()
            ->route('admin.recruitment.interviews')
            ->with('success', 'Cập nhật kết quả phỏng vấn thành công.');
    }
}
