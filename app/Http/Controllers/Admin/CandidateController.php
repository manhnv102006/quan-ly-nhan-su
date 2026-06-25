<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPost;
use App\Models\Position;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        $filters = $this->candidateFilters($request);

        $candidates = Candidate::query()
            ->with(['jobPost', 'employee'])
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];

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
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['job_post_id'] !== '', fn ($query) => $query->where('job_post_id', $filters['job_post_id']))
            ->when($filters['cv_status'] === 'has_cv', fn ($query) => $query->whereNotNull('cv_file'))
            ->when($filters['cv_status'] === 'missing_cv', fn ($query) => $query->whereNull('cv_file'))
            ->when($filters['converted'] === 'yes', fn ($query) => $query->whereNotNull('employee_id'))
            ->when($filters['converted'] === 'no', fn ($query) => $query->whereNull('employee_id'))
            ->when($filters['created_from'] !== '', fn ($query) => $query->whereDate('created_at', '>=', $filters['created_from']))
            ->when($filters['created_to'] !== '', fn ($query) => $query->whereDate('created_at', '<=', $filters['created_to']))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Candidate::count(),
            'new' => Candidate::where('status', 'new')->count(),
            'interview' => Candidate::where('status', 'interview')->count(),
            'passed' => Candidate::where('status', 'passed')->count(),
            'failed' => Candidate::where('status', 'failed')->count(),
            'converted' => Candidate::whereNotNull('employee_id')->count(),
        ];

        $jobPosts = $this->availableJobPosts();

        return view('admin.recruitment.candidates.index', compact('candidates', 'stats', 'filters', 'jobPosts'));
    }

    public function show(Candidate $candidate): View
    {
        $candidate->load([
            'jobPost.department',
            'employee',
            'emailLogs' => fn ($query) => $query->latest()->limit(10),
            'interviews' => fn ($query) => $query->with('interviewer')->latest('interview_date')->limit(10),
        ]);

        $cvData = $this->candidateCvData($candidate);

        return view('admin.recruitment.candidates.show', array_merge([
            'candidate' => $candidate,
            'departments' => $this->activeDepartments(),
            'positions' => $this->activePositions(),
            'suggestedEmployeeCode' => $this->suggestEmployeeCode($candidate),
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
            ->with('success', 'Them ung vien thanh cong.');
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
            ->with('success', 'Cap nhat ung vien thanh cong.');
    }

    public function convertToEmployee(Request $request, Candidate $candidate): RedirectResponse
    {
        if ($candidate->status !== 'passed') {
            return redirect()
                ->route('admin.recruitment.candidates.show', $candidate)
                ->with('error', 'Chi ung vien da dat moi co the chuyen thanh nhan vien.');
        }

        if ($candidate->employee_id !== null) {
            return redirect()
                ->route('admin.recruitment.candidates.show', $candidate)
                ->with('error', 'Ung vien nay da duoc chuyen thanh nhan vien.');
        }

        if (Employee::where('email', $candidate->email)->exists()) {
            return redirect()
                ->route('admin.recruitment.candidates.show', $candidate)
                ->with('error', 'Email cua ung vien da ton tai trong danh sach nhan vien.');
        }

        $validated = $request->validate([
            'employee_code' => ['required', 'string', 'max:20', 'unique:employees,employee_code'],
            'gender' => ['required', 'in:male,female,other'],
            'date_of_birth' => ['required', 'date'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'hire_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive,resigned'],
        ], [
            'employee_code.required' => 'Ma nhan vien la bat buoc.',
            'employee_code.unique' => 'Ma nhan vien da ton tai.',
            'date_of_birth.required' => 'Ngay sinh la bat buoc de tao ho so nhan vien.',
            'hire_date.required' => 'Ngay vao lam la bat buoc.',
        ]);

        $employee = DB::transaction(function () use ($candidate, $validated) {
            $employee = Employee::create([
                'employee_code' => strtoupper($validated['employee_code']),
                'full_name' => $candidate->full_name,
                'gender' => $validated['gender'],
                'date_of_birth' => $validated['date_of_birth'],
                'phone' => $candidate->phone,
                'email' => $candidate->email,
                'address' => $candidate->address,
                'department_id' => ($validated['department_id'] ?? null) ?: null,
                'position_id' => ($validated['position_id'] ?? null) ?: null,
                'hire_date' => $validated['hire_date'],
                'status' => $validated['status'],
            ]);

            $this->copyCandidateCvToEmployeeDocuments($candidate, $employee);

            $candidate->update([
                'employee_id' => $employee->id,
                'converted_at' => now(),
            ]);

            return $employee;
        });

        return redirect()
            ->route('admin.employees.show', $employee)
            ->with('success', 'Da chuyen ung vien thanh nhan vien thanh cong.');
    }

    public function destroy(Candidate $candidate): RedirectResponse
    {
        $cvPath = $candidate->cv_file;

        try {
            $candidate->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.recruitment.candidates')
                ->with('error', 'Khong the xoa ung vien vi van con du lieu lien quan trong he thong.');
        }

        $this->deleteCvFile($cvPath);

        return redirect()
            ->route('admin.recruitment.candidates')
            ->with('success', 'Xoa ung vien thanh cong.');
    }

    private function availableJobPosts()
    {
        return JobPost::query()
            ->with('department')
            ->orderBy('title')
            ->get(['id', 'department_id', 'title', 'status']);
    }

    private function activeDepartments()
    {
        return Department::query()
            ->where('status', 'active')
            ->orderBy('department_name')
            ->get(['id', 'department_name']);
    }

    private function activePositions()
    {
        return Position::query()
            ->where('status', 'active')
            ->orderBy('position_name')
            ->get(['id', 'position_name']);
    }

    private function candidateCvData(Candidate $candidate): array
    {
        $hasCvFile = filled($candidate->cv_file) && Storage::disk('public')->exists($candidate->cv_file);
        $cvUrl = $hasCvFile ? Storage::disk('public')->url($candidate->cv_file) : null;

        return compact('hasCvFile', 'cvUrl');
    }

    private function candidateFilters(Request $request): array
    {
        $status = (string) $request->string('status');
        $cvStatus = (string) $request->string('cv_status');
        $converted = (string) $request->string('converted');

        return [
            'search' => (string) $request->string('search')->trim(),
            'status' => in_array($status, ['new', 'interview', 'passed', 'failed'], true) ? $status : '',
            'job_post_id' => (string) $request->input('job_post_id', ''),
            'cv_status' => in_array($cvStatus, ['has_cv', 'missing_cv'], true) ? $cvStatus : '',
            'converted' => in_array($converted, ['yes', 'no'], true) ? $converted : '',
            'created_from' => (string) $request->input('created_from', ''),
            'created_to' => (string) $request->input('created_to', ''),
        ];
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
            'job_post_id.exists' => 'Tin tuyen dung duoc chon khong hop le.',
            'full_name.required' => 'Ho va ten ung vien la bat buoc.',
            'email.email' => 'Email ung vien khong hop le.',
            'cv_file.mimes' => 'CV chi ho tro dinh dang PDF, DOC hoac DOCX.',
            'status.in' => 'Trang thai ung vien khong hop le.',
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

    private function copyCandidateCvToEmployeeDocuments(Candidate $candidate, Employee $employee): void
    {
        if (! filled($candidate->cv_file) || ! Storage::disk('public')->exists($candidate->cv_file)) {
            return;
        }

        $extension = pathinfo($candidate->cv_file, PATHINFO_EXTENSION);
        $targetPath = 'employee-documents/'.Str::uuid().($extension ? ".{$extension}" : '');

        Storage::disk('public')->copy($candidate->cv_file, $targetPath);

        $employee->documents()->create([
            'document_name' => 'CV tu ung vien',
            'document_type' => 'cv',
            'file_path' => $targetPath,
        ]);
    }

    private function suggestEmployeeCode(Candidate $candidate): string
    {
        $baseCode = 'NV'.now()->format('ym').str_pad((string) $candidate->id, 4, '0', STR_PAD_LEFT);
        $candidateCode = Str::upper(Str::limit($baseCode, 20, ''));

        if (! Employee::where('employee_code', $candidateCode)->exists()) {
            return $candidateCode;
        }

        for ($index = 2; $index <= 99; $index++) {
            $suffix = '-'.$index;
            $code = Str::upper(Str::limit($baseCode, 20 - strlen($suffix), '').$suffix);

            if (! Employee::where('employee_code', $code)->exists()) {
                return $code;
            }
        }

        return Str::upper(Str::limit($baseCode.'-'.Str::random(4), 20, ''));
    }
}
