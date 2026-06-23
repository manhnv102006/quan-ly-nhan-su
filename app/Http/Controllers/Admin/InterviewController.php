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
        ];

        return view('admin.recruitment.interviews.index', compact('interviews', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'candidate_id' => ['required', 'exists:candidates,id'],
            'interviewer_id' => ['nullable', 'exists:employees,id'],
            'interview_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ], [
            'candidate_id.required' => 'Ứng viên là bắt buộc.',
            'candidate_id.exists' => 'Ứng viên được chọn không hợp lệ.',
            'interviewer_id.exists' => 'Người phỏng vấn được chọn không hợp lệ.',
            'interview_date.required' => 'Thời gian phỏng vấn là bắt buộc.',
            'interview_date.date' => 'Thời gian phỏng vấn không hợp lệ.',
            'note.string' => 'Ghi chú không hợp lệ.',
        ]);

        $validated['interviewer_id'] = $validated['interviewer_id'] ?: null;
        $validated['result'] = 'pending';

        DB::transaction(function () use ($validated) {
            $interview = Interview::create($validated);

            $interview->candidate()->update([
                'status' => 'interview',
            ]);
        });

        return redirect()
            ->route('admin.recruitment.interviews')
            ->with('success', 'Tạo lịch phỏng vấn thành công.');
    }
}
