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
            'candidates' => Candidate::count(),
            'interviews' => Interview::count(),
            'passed_candidates' => Candidate::where('status', 'passed')->count(),
        ];

        return view('admin.recruitment.index', compact('stats'));
    }
}
