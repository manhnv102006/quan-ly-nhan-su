<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Interview;
use App\Models\JobPost;
use App\Services\ManagerScopeService;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\UpdateInterviewEvaluationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecruitmentController extends Controller
{
    public function __construct(private readonly ManagerScopeService $managerScope)
    {
    }

    public function index(): View
    {
        $manager = $this->managerScope->resolveManagerEmployee(Auth::user());
        $departmentIds = $manager ? $this->managerScope->managedDepartmentIds($manager) : [];

        $departmentJobPosts = collect();
        if ($departmentIds !== []) {
            $departmentJobPosts = JobPost::query()
                ->with(['department', 'submittedBy'])
                ->whereIn('department_id', $departmentIds)
                ->latest()
                ->limit(20)
                ->get();
        }

        $interviewsQuery = Interview::query()
            ->with(['candidate.jobPost.department', 'interviewer'])
            ->when($manager, function ($query) use ($manager, $departmentIds) {
                $query->where(function ($scoped) use ($manager, $departmentIds) {
                    $scoped->where('interviewer_id', $manager->id);

                    if ($departmentIds !== []) {
                        $scoped->orWhereHas(
                            'candidate.jobPost',
                            fn ($jobPostQuery) => $jobPostQuery->whereIn('department_id', $departmentIds)
                        );
                    }
                });
            }, fn ($query) => $query->whereRaw('1 = 0'))
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $statsBase = clone $interviewsQuery;

        $stats = [
            'total' => (clone $statsBase)->count(),
            'pending' => (clone $statsBase)->where('result', 'pending')->count(),
            'passed' => (clone $statsBase)->where('result', 'passed')->count(),
            'failed' => (clone $statsBase)->where('result', 'failed')->count(),
            'scheduled' => (clone $statsBase)->where('status', 'scheduled')->count(),
            'completed' => (clone $statsBase)->where('status', 'completed')->count(),
            'cancelled' => (clone $statsBase)->where('status', 'cancelled')->count(),
            'no_show' => (clone $statsBase)->where('status', 'no_show')->count(),
        ];

        $interviews = $interviewsQuery->paginate(12)->withQueryString();

        return view('manager.recruitment.index', [
            'interviews' => $interviews,
            'stats' => $stats,
            'departmentJobPosts' => $departmentJobPosts,
            'manager' => $manager,
        ]);
    }

    public function createJobPost(): View|RedirectResponse
    {
        $manager = $this->managerScope->resolveManagerEmployee(Auth::user());

        if (! $manager) {
            return redirect()
                ->route('manager.recruitment.index')
                ->with('error', 'Tài khoản chưa liên kết hồ sơ quản lý.');
        }

        $departmentIds = $this->managerScope->managedDepartmentIds($manager);

        if ($departmentIds === []) {
            return redirect()
                ->route('manager.recruitment.index')
                ->with('error', 'Bạn chưa được gắn phòng ban quản lý.');
        }

        $managedDepartments = Department::query()
            ->whereIn('id', $departmentIds)
            ->orderBy('department_name')
            ->get(['id', 'department_name']);

        return view('manager.recruitment.create', compact('managedDepartments'));
    }

    public function storeJobPost(Request $request): RedirectResponse
    {
        $manager = $this->managerScope->resolveManagerEmployeeOrFail(Auth::user());
        $departmentIds = $this->managerScope->managedDepartmentIds($manager);

        abort_if($departmentIds === [], 403);

        $validated = $request->validate([
            'department_id' => ['required', 'integer', 'in:'.implode(',', $departmentIds)],
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
        ], [
            'department_id.required' => 'Phòng ban là bắt buộc.',
            'department_id.in' => 'Bạn chỉ có thể tạo tin cho phòng ban mình quản lý.',
            'title.required' => 'Tiêu đề tin tuyển dụng là bắt buộc.',
            'quantity.min' => 'Số lượng tuyển phải lớn hơn 0.',
        ]);

        $recruiterId = Department::query()->whereKey($validated['department_id'])->value('manager_id');

        JobPost::create([
            'department_id' => $validated['department_id'],
            'recruiter_id' => $recruiterId,
            'submitted_by_employee_id' => $manager->id,
            'title' => $validated['title'],
            'quantity' => $validated['quantity'],
            'salary_min' => $validated['salary_min'] ?? null,
            'salary_max' => $validated['salary_max'] ?? null,
            'work_location' => $validated['work_location'] ?? null,
            'work_type' => $validated['work_type'] ?? null,
            'application_deadline' => $validated['application_deadline'] ?? null,
            'description' => $validated['description'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'benefits' => $validated['benefits'] ?? null,
            'status' => 'pending_approval',
        ]);

        return redirect()
            ->route('manager.recruitment.index')
            ->with('success', 'Đã gửi tin tuyển dụng. Admin sẽ duyệt trước khi hiển thị công khai.');
    }

    public function updateInterview(UpdateInterviewEvaluationRequest $request, Interview $interview): RedirectResponse
    {
        $manager = $this->managerScope->resolveManagerEmployeeOrFail(Auth::user());
        $this->ensureManagerCanAccessInterview($interview, $manager);

        $validated = $request->validated();

        DB::transaction(function () use ($interview, $validated) {
            $interview->update([
                'status' => $validated['status'],
                'result' => $validated['result'],
                'technical_score' => $validated['technical_score'] ?? null,
                'attitude_score' => $validated['attitude_score'] ?? null,
                'culture_score' => $validated['culture_score'] ?? null,
                'overall_score' => $validated['overall_score'] ?? null,
                'recommendation' => $validated['recommendation'] ?? null,
                'strengths' => $validated['strengths'] ?? null,
                'weaknesses' => $validated['weaknesses'] ?? null,
                'note' => $validated['note'] ?? null,
            ]);

            $candidateStatus = match ($validated['result']) {
                'passed' => 'passed',
                'failed' => 'failed',
                default => 'interview',
            };

            $candidate = $interview->candidate;

            if ($candidate !== null) {
                $candidate->update([
                    'status' => $candidateStatus,
                ]);
            }
        });

        return redirect()
            ->route('manager.recruitment.index')
            ->with('success', 'Cập nhật kết quả phỏng vấn thành công.');
    }

    private function ensureManagerCanAccessInterview(Interview $interview, Employee $manager): void
    {
        $interview->loadMissing('candidate.jobPost');

        $departmentIds = $this->managerScope->managedDepartmentIds($manager);
        $jobDepartmentId = $interview->candidate?->jobPost?->department_id;

        $allowed = $interview->interviewer_id === $manager->id
            || ($jobDepartmentId !== null && in_array((int) $jobDepartmentId, $departmentIds, true));

        abort_unless($allowed, 403);
    }
}
