<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\JobPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicRecruitmentController extends Controller
{
    public function index(): View
    {
        $homepageJobPosts = JobPost::query()
            ->publiclyListed()
            ->with(['department', 'recruiter'])
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'open_jobs' => JobPost::query()->publiclyListed()->count(),
            'applications' => Candidate::count(),
            'departments' => JobPost::query()
                ->publiclyListed()
                ->whereNotNull('department_id')
                ->distinct('department_id')
                ->count('department_id'),
        ];

        return view('public.recruitment.index', compact('homepageJobPosts', 'stats'));
    }

    public function about(): View
    {
        return view('public.recruitment.about');
    }

    public function ecosystem(): View
    {
        return view('public.recruitment.ecosystem');
    }

    public function news(): View
    {
        return view('public.recruitment.news');
    }

    public function jobs(): View
    {
        $jobPosts = JobPost::query()
            ->publiclyListed()
            ->with('department')
            ->latest()
            ->paginate(9);

        return view('public.recruitment.jobs', compact('jobPosts'));
    }

    public function show(JobPost $publicJobPost): View
    {
        $jobPost = $this->publicJobPost($publicJobPost);

        return view('public.recruitment.show', compact('jobPost'));
    }

    public function apply(JobPost $publicJobPost): View
    {
        $jobPost = $this->publicJobPost($publicJobPost);

        return view('public.recruitment.apply', compact('jobPost'));
    }

    public function switchLocale(string $locale): RedirectResponse
    {
        if (! in_array($locale, ['vi', 'en'], true)) {
            $locale = 'vi';
        }

        session(['public_recruitment_locale' => $locale]);

        return redirect()->back();
    }

    public function store(Request $request, JobPost $publicJobPost): RedirectResponse
    {
        $jobPost = $this->publicJobPost($publicJobPost);

        $validated = $this->validateApplication($request);
        $cvPath = $request->file('cv_file')->store('candidate-cvs', 'public');

        Candidate::create([
            'job_post_id' => $jobPost->id,
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'address' => '',
            'birth_date' => null,
            'cv_file' => $cvPath,
            'status' => 'new',
        ]);

        return redirect()
            ->route('public.recruitment.show', $jobPost)
            ->with('application_success', __('recruitment.apply.success'));
    }

    private function publicJobPost(JobPost $jobPost): JobPost
    {
        return JobPost::query()
            ->publiclyListed()
            ->with(['department', 'recruiter'])
            ->whereKey($jobPost->getKey())
            ->firstOrFail();
    }

    private function validateApplication(Request $request): array
    {
        $request->merge([
            'phone' => $this->normalizeVietnamesePhone($request->input('phone')),
        ]);

        return $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^0(3|5|7|8|9)\d{8}$/', 'unique:candidates,phone'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:candidates,email'],
            'cv_file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx'],
        ], [
            'full_name.required' => __('recruitment.validation.full_name_required'),
            'phone.required' => __('recruitment.validation.phone_required'),
            'phone.regex' => __('recruitment.validation.phone_regex'),
            'phone.unique' => __('recruitment.validation.phone_unique'),
            'email.required' => __('recruitment.validation.email_required'),
            'email.email' => __('recruitment.validation.email_email'),
            'email.unique' => __('recruitment.validation.email_unique'),
            'cv_file.required' => __('recruitment.validation.cv_required'),
            'cv_file.mimes' => __('recruitment.validation.cv_mimes'),
            'cv_file.max' => __('recruitment.validation.cv_max'),
        ]);
    }

    private function normalizeVietnamesePhone(?string $phone): string
    {
        $normalized = preg_replace('/[\s.\-]/', '', trim((string) $phone));

        if (preg_match('/^\+84(\d{9})$/', $normalized, $matches)) {
            return '0'.$matches[1];
        }

        if (preg_match('/^84(\d{9})$/', $normalized, $matches)) {
            return '0'.$matches[1];
        }

        return $normalized;
    }
}
