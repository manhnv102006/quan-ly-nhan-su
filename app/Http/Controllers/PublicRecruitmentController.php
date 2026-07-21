<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicRecruitmentController extends Controller
{
    public function index(): View
    {
        $jobPosts = JobPost::query()
            ->publiclyVisible()
            ->with('department')
            ->latest()
            ->paginate(9);

        return view('public.recruitment.index', compact('jobPosts'));
    }

    public function show(JobPost $jobPost): View
    {
        $jobPost = $this->publicJobPost($jobPost);

        return view('public.recruitment.show', compact('jobPost'));
    }

    public function apply(JobPost $jobPost): View
    {
        $jobPost = $this->publicJobPost($jobPost);

        return view('public.recruitment.apply', compact('jobPost'));
    }

    public function store(Request $request, JobPost $jobPost): RedirectResponse
    {
        abort(404);
    }

    private function publicJobPost(JobPost $jobPost): JobPost
    {
        return JobPost::query()
            ->publiclyVisible()
            ->with(['department', 'recruiter'])
            ->whereKey($jobPost->getKey())
            ->firstOrFail();
    }
}
