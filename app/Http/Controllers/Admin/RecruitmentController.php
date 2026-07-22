<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\JobPost;
use Illuminate\View\View;

class RecruitmentController extends Controller
{
    public function index(): View
    {
        $stats = [
            'job_posts' => JobPost::count(),
            'open_job_posts' => JobPost::where('status', 'open')->count(),
            'closed_job_posts' => JobPost::where('status', 'closed')->count(),
            'candidates' => Candidate::count(),
            'pending_candidates' => Candidate::where('status', 'new')->count(),
            'interview_candidates' => Candidate::where('status', 'interview')->count(),
            'interviews' => Interview::count(),
            'passed_candidates' => Candidate::where('status', 'passed')->count(),
            'failed_candidates' => Candidate::where('status', 'failed')->count(),
            'converted_candidates' => Candidate::whereNotNull('employee_id')->count(),
        ];

        $recentCandidates = Candidate::query()
            ->with('jobPost:id,title')
            ->latest()
            ->limit(5)
            ->get(['id', 'full_name', 'email', 'status', 'job_post_id', 'created_at']);

        $upcomingInterviews = Interview::query()
            ->with([
                'candidate:id,full_name',
                'interviewer:id,full_name',
            ])
            ->where('status', 'scheduled')
            ->where('interview_date', '>=', now())
            ->orderBy('interview_date')
            ->limit(5)
            ->get(['id', 'candidate_id', 'interviewer_id', 'interview_date', 'status']);

        $openJobPosts = JobPost::query()
            ->with('department:id,department_name')
            ->where('status', 'open')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get(['id', 'title', 'department_id', 'quantity', 'application_deadline', 'status']);

        return view('admin.recruitment.index', compact(
            'stats',
            'recentCandidates',
            'upcomingInterviews',
            'openJobPosts',
        ));
    }
}
