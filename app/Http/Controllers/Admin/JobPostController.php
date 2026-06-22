<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\JobPost;
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

        $validated['department_id'] = $validated['department_id'] ?: null;

        JobPost::create($validated);

        return redirect()
            ->route('admin.recruitment.job-posts')
            ->with('success', 'Thêm tin tuyển dụng thành công.');
    }

    public function edit(JobPost $jobPost): View
    {
        $search = '';
        $data = $this->jobPostListData($search);

        return view('admin.recruitment.job-posts.index', array_merge($data, [
            'departments' => $this->activeDepartments(),
            'showCreateForm' => false,
            'showEditForm' => true,
            'editingJobPost' => $jobPost->load('department'),
        ]));
    }

    public function update(Request $request, JobPost $jobPost): RedirectResponse
    {
        $validated = $this->validateJobPost($request);

        $validated['department_id'] = $validated['department_id'] ?: null;

        $jobPost->update($validated);

        return redirect()
            ->route('admin.recruitment.job-posts')
            ->with('success', 'Cập nhật tin tuyển dụng thành công.');
    }

    private function jobPostListData(string $search): array
    {
        $jobPosts = JobPost::query()
            ->with('department')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('department', function ($departmentQuery) use ($search) {
                            $departmentQuery->where('department_name', 'like', "%{$search}%");
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

    private function validateJobPost(Request $request): array
    {
        return $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'title' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:open,closed'],
        ], [
            'department_id.exists' => 'Phòng ban được chọn không hợp lệ.',
            'title.required' => 'Tiêu đề tin tuyển dụng là bắt buộc.',
            'title.max' => 'Tiêu đề tin tuyển dụng không được vượt quá 255 ký tự.',
            'quantity.required' => 'Số lượng tuyển là bắt buộc.',
            'quantity.integer' => 'Số lượng tuyển phải là số nguyên.',
            'quantity.min' => 'Số lượng tuyển phải lớn hơn 0.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái tin tuyển dụng không hợp lệ.',
        ]);
    }
}
