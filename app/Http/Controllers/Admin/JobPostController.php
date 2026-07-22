<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
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

        $validated = $this->applyDepartmentRecruiter($validated);

        JobPost::create($validated);

        return redirect()
            ->route('admin.recruitment.job-posts')
            ->with('success', 'Thêm tin tuyển dụng thành công.');
    }

    public function show(JobPost $jobPost): View
    {
        $jobPost->load(['department.manager', 'recruiter', 'submittedBy']);
        $jobPost->loadCount([
            'candidates',
            'candidates as candidates_new_count' => fn ($query) => $query->where('status', 'new'),
            'candidates as candidates_interview_count' => fn ($query) => $query->where('status', 'interview'),
            'candidates as candidates_passed_count' => fn ($query) => $query->where('status', 'passed'),
        ]);

        $recentCandidates = $jobPost->candidates()
            ->latest()
            ->limit(15)
            ->get(['id', 'full_name', 'email', 'phone', 'status', 'created_at']);

        return view('admin.recruitment.job-posts.show', compact('jobPost', 'recentCandidates'));
    }

    public function edit(JobPost $jobPost): View
    {
        $filters = $this->defaultFilters();
        $data = $this->jobPostListData($filters);

        return view('admin.recruitment.job-posts.index', array_merge($data, [
            'departments' => $this->activeDepartments(),
            'showCreateForm' => false,
            'showEditForm' => true,
            'editingJobPost' => $jobPost->load(['department.manager', 'recruiter']),
        ]));
    }

    public function update(Request $request, JobPost $jobPost): RedirectResponse
    {
        if ($jobPost->status === 'pending_approval') {
            return redirect()
                ->route('admin.recruitment.job-posts')
                ->with('error', 'Tin đang chờ duyệt: hãy dùng nút Duyệt hoặc Từ chối.');
        }

        $validated = $this->validateJobPost($request);

        $validated = $this->applyDepartmentRecruiter($validated);

        $jobPost->update($validated);

        return redirect()
            ->route('admin.recruitment.job-posts')
            ->with('success', 'Cập nhật tin tuyển dụng thành công.');
    }

    public function updateStatus(Request $request, JobPost $jobPost): RedirectResponse
    {
        if (in_array($jobPost->status, ['pending_approval', 'rejected'], true)) {
            return redirect()
                ->back()
                ->with('error', 'Tin chờ duyệt cần dùng nút Duyệt hoặc Từ chối.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:open,closed'],
        ], [
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái tin tuyển dụng không hợp lệ.',
        ]);

        $jobPost->update(['status' => $validated['status']]);

        $redirectTo = $request->headers->get('referer', route('admin.recruitment.job-posts'));

        return redirect()
            ->to($redirectTo)
            ->with('success', 'Đã cập nhật trạng thái tin tuyển dụng.');
    }

    public function approve(JobPost $jobPost): RedirectResponse
    {
        if ($jobPost->status !== 'pending_approval') {
            return redirect()
                ->route('admin.recruitment.job-posts')
                ->with('error', 'Chỉ có thể duyệt tin đang chờ phê duyệt.');
        }

        $jobPost->update(['status' => 'open']);

        return redirect()
            ->back()
            ->with('success', 'Đã duyệt tin tuyển dụng. Tin đang hiển thị công khai.');
    }

    public function reject(Request $request, JobPost $jobPost): RedirectResponse
    {
        if ($jobPost->status !== 'pending_approval') {
            return redirect()
                ->route('admin.recruitment.job-posts')
                ->with('error', 'Chỉ có thể từ chối tin đang chờ phê duyệt.');
        }

        $jobPost->update(['status' => 'rejected']);

        return redirect()
            ->back()
            ->with('success', 'Đã từ chối tin tuyển dụng do manager gửi.');
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
            ->with(['department', 'recruiter', 'submittedBy'])
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
            'pending_approval' => JobPost::where('status', 'pending_approval')->count(),
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
            'status' => in_array($status, ['open', 'closed', 'pending_approval', 'rejected'], true) ? $status : '',
            'department_id' => (string) $request->input('department_id', ''),
        ];
    }

    private function activeDepartments()
    {
        return Department::query()
            ->where('status', 'active')
            ->with('manager:id,full_name,employee_code')
            ->orderBy('department_name')
            ->get(['id', 'department_name', 'manager_id']);
    }

    private function applyDepartmentRecruiter(array $validated): array
    {
        $validated['department_id'] = ($validated['department_id'] ?? null) ?: null;

        if ($validated['department_id'] === null) {
            $validated['recruiter_id'] = null;

            return $validated;
        }

        $managerId = Department::query()
            ->whereKey($validated['department_id'])
            ->value('manager_id');

        $validated['recruiter_id'] = $managerId ?: null;

        return $validated;
    }

    private function validateJobPost(Request $request): array
    {
        return $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'title' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0'],
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
            'title.required' => 'Tiêu đề tin tuyển dụng là bắt buộc.',
            'title.max' => 'Tiêu đề tin tuyển dụng không được vượt quá 255 ký tự.',
            'quantity.required' => 'Số lượng tuyển là bắt buộc.',
            'quantity.integer' => 'Số lượng tuyển phải là số nguyên.',
            'quantity.min' => 'Số lượng tuyển không được âm.',
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
