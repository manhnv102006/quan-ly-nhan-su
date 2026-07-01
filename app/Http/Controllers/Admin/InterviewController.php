<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Employee;
use App\Models\Interview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InterviewController extends Controller
{
    public function create(): View
    {
        $candidates = Candidate::query()
            ->with('jobPost')
            ->orderBy('full_name')
            ->get(['id', 'job_post_id', 'full_name', 'status']);

        $interviewers = Employee::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code']);

        return view('admin.recruitment.interviews.create', compact('candidates', 'interviewers'));
    }

    public function index(): View
    {
        $interviews = Interview::query()
            ->with(['candidate.jobPost', 'interviewer'])
            ->orderByDesc('interview_date')
            ->paginate(12)
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
            'interviewer_id' => ['nullable', 'exists:employees,id'],
            'interview_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ], [
            'candidate_id.required' => 'Ứng viên là bắt buộc.',
            'candidate_id.exists' => 'Ứng viên được chọn không hợp lệ.',
            'candidate_id.unique' => 'Đã tạo lịch phỏng vấn cho ứng viên này rồi.',
            'interviewer_id.exists' => 'Người phỏng vấn được chọn không hợp lệ.',
            'interview_date.required' => 'Thời gian phỏng vấn là bắt buộc.',
            'interview_date.date' => 'Thời gian phỏng vấn không hợp lệ.',
        ]);

        $validated['interviewer_id'] = $validated['interviewer_id'] ?: null;
        $validated['status'] = 'scheduled';
        $validated['result'] = 'pending';

        DB::transaction(function () use ($validated) {
            $candidate = Candidate::query()->findOrFail($validated['candidate_id']);

            Interview::create($validated);

            $candidate->update([
                'status' => 'interview',
            ]);
        });

        return redirect()
            ->route('admin.recruitment.interviews')
            ->with('success', 'Tạo lịch phỏng vấn thành công.');
    }

    public function update(Request $request, Interview $interview): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:scheduled,completed,cancelled,no_show'],
            'result' => ['required', 'in:pending,passed,failed'],
            'technical_score' => ['nullable', 'integer', 'between:0,10'],
            'attitude_score' => ['nullable', 'integer', 'between:0,10'],
            'culture_score' => ['nullable', 'integer', 'between:0,10'],
            'overall_score' => ['nullable', 'integer', 'between:0,10'],
            'recommendation' => ['nullable', 'in:hire,consider,reject'],
            'strengths' => ['nullable', 'string'],
            'weaknesses' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
        ], [
            'status.required' => 'Trạng thái buổi phỏng vấn là bắt buộc.',
            'status.in' => 'Trạng thái buổi phỏng vấn không hợp lệ.',
            'result.required' => 'Kết quả phỏng vấn là bắt buộc.',
            'result.in' => 'Kết quả phỏng vấn không hợp lệ.',
            'technical_score.between' => 'Điểm kỹ thuật phải từ 0 đến 10.',
            'attitude_score.between' => 'Điểm thái độ phải từ 0 đến 10.',
            'culture_score.between' => 'Điểm phù hợp văn hóa phải từ 0 đến 10.',
            'overall_score.between' => 'Điểm tổng quan phải từ 0 đến 10.',
        ]);

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
