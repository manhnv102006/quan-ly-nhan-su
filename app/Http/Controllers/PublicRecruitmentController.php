<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\JobPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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

    public function store(Request $request, JobPost $publicJobPost): RedirectResponse
    {
        $jobPost = $this->publicJobPost($publicJobPost);

        $validated = $this->validateApplication($request);
        $cvPath = $this->storeCvFile($request->file('cv_file'));

        Candidate::create([
            'job_post_id' => $jobPost->id,
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'address' => $validated['address'],
            'birth_date' => $validated['birth_date'],
            'cv_file' => $cvPath,
            'status' => 'new',
        ]);

        return redirect()
            ->route('public.recruitment.show', $jobPost)
            ->with('application_success', 'Hồ sơ ứng tuyển của bạn đã được gửi thành công. Bộ phận tuyển dụng sẽ liên hệ sau khi xem xét.');
    }

    private function publicJobPost(JobPost $jobPost): JobPost
    {
        return JobPost::query()
            ->publiclyVisible()
            ->with(['department', 'recruiter'])
            ->whereKey($jobPost->getKey())
            ->firstOrFail();
    }

    private function validateApplication(Request $request): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/', 'unique:candidates,phone'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:candidates,email'],
            'address' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'cv_file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx'],
        ], [
            'full_name.required' => 'Họ và tên ứng viên là bắt buộc.',
            'phone.required' => 'Số điện thoại là bắt buộc.',
            'phone.regex' => 'Số điện thoại phải gồm đúng 10 chữ số.',
            'phone.unique' => 'Số điện thoại này đã tồn tại trong danh sách ứng viên.',
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email ứng viên không hợp lệ.',
            'email.unique' => 'Email này đã tồn tại trong danh sách ứng viên.',
            'address.required' => 'Địa chỉ là bắt buộc.',
            'birth_date.required' => 'Ngày sinh là bắt buộc.',
            'birth_date.date' => 'Ngày sinh không hợp lệ.',
            'cv_file.mimes' => 'CV chỉ hỗ trợ định dạng PDF, DOC hoặc DOCX.',
            'cv_file.max' => 'CV không được vượt quá 10MB.',
        ]);
    }

    private function storeCvFile(?UploadedFile $file): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        return $file->store('candidate-cvs', 'public');
    }
}
