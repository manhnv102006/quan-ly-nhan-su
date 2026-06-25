<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPost;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobPostController extends Controller
{
    public function create(): View
    {
        $search = '';
        $data = $this->jobPostListData($search);

        return view('admin.recruitment.job-posts.index', array_merge($data, [
            'departments' => $this->activeDepartments(),
            'recruiters' => $this->activeRecruiters(),
            'showCreateForm' => true,
            'showEditForm' => false,
        ]));
    }

    public function index(Request $request): View
    {
        $search = (string) $request->string('search')->trim();
        $data = $this->jobPostListData($search);

        return view('admin.recruitment.job-posts.index', array_merge($data, [
            'showCreateForm' => false,
            'showEditForm' => false,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateJobPost($request);

        $validated['department_id'] = ($validated['department_id'] ?? null) ?: null;
        $validated['recruiter_id'] = ($validated['recruiter_id'] ?? null) ?: null;

        JobPost::create($validated);

        return redirect()
            ->route('admin.recruitment.job-posts')
            ->with('success', 'Them tin tuyen dung thanh cong.');
    }

    public function edit(JobPost $jobPost): View
    {
        $search = '';
        $data = $this->jobPostListData($search);

        return view('admin.recruitment.job-posts.index', array_merge($data, [
            'departments' => $this->activeDepartments(),
            'recruiters' => $this->activeRecruiters(),
            'showCreateForm' => false,
            'showEditForm' => true,
            'editingJobPost' => $jobPost->load(['department', 'recruiter']),
        ]));
    }

    public function update(Request $request, JobPost $jobPost): RedirectResponse
    {
        $validated = $this->validateJobPost($request);

        $validated['department_id'] = ($validated['department_id'] ?? null) ?: null;
        $validated['recruiter_id'] = ($validated['recruiter_id'] ?? null) ?: null;

        $jobPost->update($validated);

        return redirect()
            ->route('admin.recruitment.job-posts')
            ->with('success', 'Cap nhat tin tuyen dung thanh cong.');
    }

    public function destroy(JobPost $jobPost): RedirectResponse
    {
        try {
            $jobPost->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.recruitment.job-posts')
                ->with('error', 'Khong the xoa tin tuyen dung vi van con du lieu lien quan trong he thong.');
        }

        return redirect()
            ->route('admin.recruitment.job-posts')
            ->with('success', 'Xoa tin tuyen dung thanh cong.');
    }

    private function jobPostListData(string $search): array
    {
        $jobPosts = JobPost::query()
            ->with(['department', 'recruiter'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('requirements', 'like', "%{$search}%")
                        ->orWhere('benefits', 'like', "%{$search}%")
                        ->orWhere('work_location', 'like', "%{$search}%")
                        ->orWhereHas('department', function ($departmentQuery) use ($search) {
                            $departmentQuery->where('department_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('recruiter', function ($recruiterQuery) use ($search) {
                            $recruiterQuery->where('full_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => JobPost::count(),
            'open' => JobPost::where('status', 'open')->count(),
            'closed' => JobPost::where('status', 'closed')->count(),
        ];

        return compact('jobPosts', 'search', 'stats');
    }

    private function activeDepartments()
    {
        return Department::query()
            ->where('status', 'active')
            ->orderBy('department_name')
            ->get(['id', 'department_name']);
    }

    private function activeRecruiters()
    {
        return Employee::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code']);
    }

    private function validateJobPost(Request $request): array
    {
        return $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'recruiter_id' => ['nullable', 'exists:employees,id'],
            'title' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'gte:salary_min'],
            'work_location' => ['nullable', 'string', 'max:255'],
            'work_type' => ['nullable', 'in:full_time,part_time,remote,hybrid,contract'],
            'application_deadline' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],
            'status' => ['required', 'in:open,closed'],
        ], [
            'department_id.exists' => 'Phong ban duoc chon khong hop le.',
            'recruiter_id.exists' => 'Nguoi phu trach tuyen dung khong hop le.',
            'title.required' => 'Tieu de tin tuyen dung la bat buoc.',
            'title.max' => 'Tieu de tin tuyen dung khong duoc vuot qua 255 ky tu.',
            'quantity.required' => 'So luong tuyen la bat buoc.',
            'quantity.integer' => 'So luong tuyen phai la so nguyen.',
            'quantity.min' => 'So luong tuyen phai lon hon 0.',
            'salary_min.numeric' => 'Luong toi thieu phai la so.',
            'salary_max.numeric' => 'Luong toi da phai la so.',
            'salary_max.gte' => 'Luong toi da phai lon hon hoac bang luong toi thieu.',
            'work_type.in' => 'Hinh thuc lam viec khong hop le.',
            'application_deadline.date' => 'Han nop ho so khong hop le.',
            'status.required' => 'Trang thai la bat buoc.',
            'status.in' => 'Trang thai tin tuyen dung khong hop le.',
        ]);
    }
}
