<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\JobPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CandidateController extends Controller
{
    public function create(): View
    {
        $jobPosts = $this->availableJobPosts();

        return view('admin.recruitment.candidates.create', compact('jobPosts'));
    }

    public function index(Request $request): View
    {
        $search = (string) $request->string('search')->trim();

        $candidates = Candidate::query()
            ->with('jobPost')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhereHas('jobPost', function ($jobPostQuery) use ($search) {
                            $jobPostQuery->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Candidate::count(),
            'new' => Candidate::where('status', 'new')->count(),
            'interview' => Candidate::where('status', 'interview')->count(),
            'passed' => Candidate::where('status', 'passed')->count(),
        ];

        return view('admin.recruitment.candidates.index', compact('candidates', 'stats', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'job_post_id' => ['nullable', 'exists:job_posts,id'],
            'full_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'status' => ['required', 'in:new,interview,passed,failed'],
        ], [
            'job_post_id.exists' => 'Tin tuyá»ƒn dá»¥ng Ä‘Æ°á»£c chá»n khĂ´ng há»£p lá»‡.',
            'full_name.required' => 'Há» vĂ  tĂªn á»©ng viĂªn lĂ  báº¯t buá»™c.',
            'full_name.max' => 'Há» vĂ  tĂªn á»©ng viĂªn khĂ´ng Ä‘Æ°á»£c vÆ°á»£t quĂ¡ 100 kĂ½ tá»±.',
            'phone.required' => 'Sá»‘ Ä‘iá»‡n thoáº¡i lĂ  báº¯t buá»™c.',
            'phone.max' => 'Sá»‘ Ä‘iá»‡n thoáº¡i khĂ´ng Ä‘Æ°á»£c vÆ°á»£t quĂ¡ 20 kĂ½ tá»±.',
            'email.required' => 'Email lĂ  báº¯t buá»™c.',
            'email.email' => 'Email á»©ng viĂªn khĂ´ng há»£p lá»‡.',
            'email.max' => 'Email á»©ng viĂªn khĂ´ng Ä‘Æ°á»£c vÆ°á»£t quĂ¡ 100 kĂ½ tá»±.',
            'address.required' => 'Äá»‹a chá»‰ lĂ  báº¯t buá»™c.',
            'address.max' => 'Äá»‹a chá»‰ khĂ´ng Ä‘Æ°á»£c vÆ°á»£t quĂ¡ 255 kĂ½ tá»±.',
            'birth_date.date' => 'NgĂ y sinh khĂ´ng há»£p lá»‡.',
            'status.required' => 'Tráº¡ng thĂ¡i á»©ng viĂªn lĂ  báº¯t buá»™c.',
            'status.in' => 'Tráº¡ng thĂ¡i á»©ng viĂªn khĂ´ng há»£p lá»‡.',
        ]);

        $validated['job_post_id'] = $validated['job_post_id'] ?: null;

        Candidate::create($validated);

        return redirect()
            ->route('admin.recruitment.candidates')
            ->with('success', 'ThĂªm á»©ng viĂªn thĂ nh cĂ´ng.');
    }

    private function availableJobPosts()
    {
        return JobPost::query()
            ->with('department')
            ->orderBy('title')
            ->get(['id', 'department_id', 'title', 'status']);
    }
}
