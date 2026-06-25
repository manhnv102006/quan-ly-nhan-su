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
        $filters = $this->defaultFilters();
        $data = $this->jobPostListData($filters);

        return view('admin.recruitment.job-posts.index', array_merge($data, [
            'departments' => $this->activeDepartments(),
            'recruiters' => $this->activeRecruiters(),
            'showCreateForm' => true,
            'showEditForm' => false,
        ]));
    }

    public function index(Request $request): View
    {
        $filters = $this->jobPostFilters($request);
        $data = $this->jobPostListData($filters);

        return view('admin.recruitment.job-posts.index', array_merge($data, [
            'departments' => $this->activeDepartments(),
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
            ->with('success', 'Thêm tin tuyển dụng thành công.');
    }

    public function edit(JobPost $jobPost): View
    {
        $filters = $this->defaultFilters();
        $data = $this->jobPostListData($filters);

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
            ->with('success', 'Cập nhật tin tuyển dụng thành công.');
    }

    public function destroy(JobPost $jobPost): RedirectResponse
    {
        try {
            $jobPost->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.recruitment.job-posts')
                ->with('error', 'Không thể xóa tin tuyển dụng vì vẫn còn dữ liệu liên quan trong hệ thống.');
        }

        return redirect()
            ->route('admin.recruitment.job-posts')
            ->with('success', 'Xóa tin tuyển dụng thành công.');
    }

    private function jobPostListData(array $filters): array
    {
        $search = $filters['search'];

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
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['department_id'] !== '', fn ($query) => $query->where('department_id', $filters['department_id']))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => JobPost::count(),
            'open' => JobPost::where('status', 'open')->count(),
            'closed' => JobPost::where('status', 'closed')->count(),
        ];

        return compact('jobPosts', 'search', 'stats', 'filters');
    }

    private function defaultFilters(): array
    {
        return [
            'search' => '',
            'status' => '',
            'department_id' => '',
        ];
    }

    private function jobPostFilters(Request $request): array
    {
        $status = (string) $request->string('status');

        return [
            'search' => (string) $request->string('search')->trim(),
            'status' => in_array($status, ['open', 'closed'], true) ? $status : '',
            'department_id' => (string) $request->input('department_id', ''),
        ];
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
            'department_id.exists' => 'Phòng ban được chọn không hợp lệ.',
            'recruiter_id.exists' => 'Người phụ trách tuyển dụng không hợp lệ.',
            'title.required' => 'Tiêu đề tin tuyển dụng là bắt buộc.',
            'title.max' => 'Tiêu đề tin tuyển dụng không được vượt quá 255 ký tự.',
            'quantity.required' => 'Số lượng tuyển là bắt buộc.',
            'quantity.integer' => 'Số lượng tuyển phải là số nguyên.',
            'quantity.min' => 'Số lượng tuyển phải lớn hơn 0.',
            'salary_min.numeric' => 'Lương tối thiểu phải là số.',
            'salary_max.numeric' => 'Lương tối đa phải là số.',
            'salary_max.gte' => 'Lương tối đa phải lớn hơn hoặc bằng lương tối thiểu.',
            'work_type.in' => 'Hình thức làm việc không hợp lệ.',
            'application_deadline.date' => 'Hạn nộp hồ sơ không hợp lệ.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái tin tuyển dụng không hợp lệ.',
        ]);
    }
}
