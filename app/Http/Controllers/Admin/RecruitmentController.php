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
            'interviewed_candidates' => Candidate::whereHas('interviews')->count(),
            'interviews' => Interview::count(),
            'passed_candidates' => Candidate::where('status', 'passed')->count(),
            'failed_candidates' => Candidate::where('status', 'failed')->count(),
            'converted_candidates' => Candidate::whereNotNull('employee_id')->count(),
        ];

        return view('admin.recruitment.index', compact('stats'));
    }
}
