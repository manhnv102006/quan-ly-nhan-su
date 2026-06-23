<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\JobPost;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CandidateController extends Controller
{
    public function create(): View
    {
        $jobPosts = $this->availableJobPosts();

        return view('admin.recruitment.candidates.create', compact('jobPosts'));
    }

    public function edit(Candidate $candidate): View
    {
        $jobPosts = $this->availableJobPosts();
        $cvData = $this->candidateCvData($candidate);

        return view('admin.recruitment.candidates.edit', array_merge([
            'candidate' => $candidate,
            'jobPosts' => $jobPosts,
        ], $cvData));
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

    public function show(Candidate $candidate): View
    {
        $candidate->load('jobPost.department');
        $cvData = $this->candidateCvData($candidate);

        return view('admin.recruitment.candidates.show', array_merge([
            'candidate' => $candidate,
        ], $cvData));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCandidate($request);

        $validated['job_post_id'] = $validated['job_post_id'] ?: null;
        $validated['cv_file'] = $this->storeCvFile($request->file('cv_file'));

        Candidate::create($validated);

        return redirect()
            ->route('admin.recruitment.candidates')
            ->with('success', 'Thêm ứng viên thành công.');
    }

    public function update(Request $request, Candidate $candidate): RedirectResponse
    {
        $validated = $this->validateCandidate($request);

        $validated['job_post_id'] = $validated['job_post_id'] ?: null;

        $oldCvPath = $candidate->cv_file;
        $newCvPath = $this->storeCvFile($request->file('cv_file'));

        if ($newCvPath !== null) {
            $validated['cv_file'] = $newCvPath;
        } else {
            unset($validated['cv_file']);
        }

        try {
            $candidate->update($validated);
        } catch (\Throwable $exception) {
            if ($newCvPath !== null) {
                $this->deleteCvFile($newCvPath);
            }

            throw $exception;
        }

        if ($newCvPath !== null && $oldCvPath !== null && $oldCvPath !== $newCvPath) {
            $this->deleteCvFile($oldCvPath);
        }

        return redirect()
            ->route('admin.recruitment.candidates.show', $candidate)
            ->with('success', 'Cập nhật ứng viên thành công.');
    }

    public function destroy(Candidate $candidate): RedirectResponse
    {
        $cvPath = $candidate->cv_file;

        try {
            $candidate->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.recruitment.candidates')
                ->with('error', 'Không thể xóa ứng viên vì vẫn còn dữ liệu liên quan trong hệ thống.');
        }

        $this->deleteCvFile($cvPath);

        return redirect()
            ->route('admin.recruitment.candidates')
            ->with('success', 'Xóa ứng viên thành công.');
    }

    private function availableJobPosts()
    {
        return JobPost::query()
            ->with('department')
            ->orderBy('title')
            ->get(['id', 'department_id', 'title', 'status']);
    }

    private function candidateCvData(Candidate $candidate): array
    {
        $hasCvFile = filled($candidate->cv_file) && Storage::disk('public')->exists($candidate->cv_file);
        $cvUrl = $hasCvFile ? Storage::disk('public')->url($candidate->cv_file) : null;

        return compact('hasCvFile', 'cvUrl');
    }

    private function validateCandidate(Request $request): array
    {
        return $request->validate([
            'job_post_id' => ['nullable', 'exists:job_posts,id'],
            'full_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'cv_file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx'],
            'status' => ['required', 'in:new,interview,passed,failed'],
        ], [
            'job_post_id.exists' => 'Tin tuyển dụng được chọn không hợp lệ.',
            'full_name.required' => 'Họ và tên ứng viên là bắt buộc.',
            'full_name.max' => 'Họ và tên ứng viên không được vượt quá 100 ký tự.',
            'phone.required' => 'Số điện thoại là bắt buộc.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email ứng viên không hợp lệ.',
            'email.max' => 'Email ứng viên không được vượt quá 100 ký tự.',
            'address.required' => 'Địa chỉ là bắt buộc.',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'birth_date.date' => 'Ngày sinh không hợp lệ.',
            'cv_file.file' => 'CV tải lên không hợp lệ.',
            'cv_file.max' => 'CV tải lên không được vượt quá 10MB.',
            'cv_file.mimes' => 'CV chỉ hỗ trợ định dạng PDF, DOC hoặc DOCX.',
            'status.required' => 'Trạng thái ứng viên là bắt buộc.',
            'status.in' => 'Trạng thái ứng viên không hợp lệ.',
        ]);
    }

    private function storeCvFile(?UploadedFile $file): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        return $file->store('candidate-cvs', 'public');
    }

    private function deleteCvFile(?string $path): void
    {
        if (! filled($path)) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}