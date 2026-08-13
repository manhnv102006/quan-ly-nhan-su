<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateInterviewEvaluationRequest;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Interview;
use App\Models\JobPost;
use App\Services\ManagerScopeService;
use App\Services\RecruitmentInterviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RecruitmentController extends Controller
{
    public function __construct(
        private readonly ManagerScopeService $managerScope,
        private readonly RecruitmentInterviewService $interviews,
    ) {}

    public function index(): View
    {
        $manager = $this->managerScope->resolveManagerEmployee(Auth::user());
        $departmentIds = $manager ? $this->managerScope->managedDepartmentIds($manager) : [];

        $departmentJobPosts = collect();
        $candidateStats = ['total' => 0, 'new' => 0, 'interview' => 0, 'pending_hire_approval' => 0];

        if ($departmentIds !== []) {
            $departmentJobPosts = JobPost::query()
                ->with(['department', 'submittedBy'])
                ->whereIn('department_id', $departmentIds)
                ->latest()
                ->limit(20)
                ->get();

            $candidatesQuery = $this->departmentCandidatesQuery($departmentIds);
            $candidateStats = [
                'total' => (clone $candidatesQuery)->count(),
                'new' => (clone $candidatesQuery)->where('status', Candidate::STATUS_NEW)->count(),
                'interview' => (clone $candidatesQuery)->where('status', Candidate::STATUS_INTERVIEW)->count(),
                'pending_hire_approval' => (clone $candidatesQuery)->where('status', Candidate::STATUS_PENDING_HIRE_APPROVAL)->count(),
            ];
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
            'candidateStats' => $candidateStats,
            'departmentJobPosts' => $departmentJobPosts,
            'manager' => $manager,
        ]);
    }

    public function candidates(Request $request): View|RedirectResponse
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

        $status = (string) $request->query('status', '');
        $search = trim((string) $request->query('search', ''));

        $query = $this->departmentCandidatesQuery($departmentIds)
            ->with(['jobPost.department']);

        if ($status !== '' && array_key_exists($status, Candidate::statusLabels())) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $candidates = $query->latest()->paginate(15)->withQueryString();

        $statsBase = $this->departmentCandidatesQuery($departmentIds);
        $stats = [
            'total' => (clone $statsBase)->count(),
            'new' => (clone $statsBase)->where('status', Candidate::STATUS_NEW)->count(),
            'interview' => (clone $statsBase)->where('status', Candidate::STATUS_INTERVIEW)->count(),
            'pending_hire_approval' => (clone $statsBase)->where('status', Candidate::STATUS_PENDING_HIRE_APPROVAL)->count(),
            'passed' => (clone $statsBase)->where('status', Candidate::STATUS_PASSED)->count(),
            'failed' => (clone $statsBase)->where('status', Candidate::STATUS_FAILED)->count(),
        ];

        return view('manager.recruitment.candidates.index', compact('candidates', 'stats', 'status', 'search'));
    }

    public function showCandidate(Candidate $candidate): View|RedirectResponse
    {
        $manager = $this->managerScope->resolveManagerEmployeeOrFail(Auth::user());
        $this->ensureManagerCanAccessCandidate($candidate, $manager);

        $candidate->load([
            'jobPost.department.manager',
            'jobPost.position',
            'interviews.interviewer',
        ]);

        $hasCvFile = filled($candidate->cv_file) && Storage::disk('public')->exists($candidate->cv_file);
        $cvUrl = $hasCvFile ? Storage::disk('public')->url($candidate->cv_file) : null;
        $canScheduleInterview = $candidate->interviews->isEmpty();
        $departmentInterviewers = $this->departmentInterviewersForCandidate($candidate);

        return view('manager.recruitment.candidates.show', compact(
            'candidate',
            'hasCvFile',
            'cvUrl',
            'canScheduleInterview',
            'departmentInterviewers',
            'manager',
        ));
    }

    public function storeInterview(Request $request, Candidate $candidate): RedirectResponse
    {
        $manager = $this->managerScope->resolveManagerEmployeeOrFail(Auth::user());
        $this->ensureManagerCanAccessCandidate($candidate, $manager);

        if ($candidate->interviews()->exists()) {
            return back()->with('error', 'Ứng viên đã có lịch phỏng vấn.');
        }

        $allowedInterviewerIds = $this->allowedInterviewerIds($candidate);

        if ($allowedInterviewerIds->isEmpty()) {
            return back()->with('error', 'Phòng ban chưa có nhân viên để phân công phỏng vấn.');
        }

        $validated = $request->validate([
            'interviewer_id' => ['required', 'integer', 'in:'.$allowedInterviewerIds->implode(',')],
            'interview_date' => ['required', 'date', 'after:now'],
            'note' => ['nullable', 'string'],
        ], [
            'interviewer_id.required' => 'Vui lòng chọn người phỏng vấn.',
            'interviewer_id.in' => 'Người phỏng vấn phải là thành viên cùng phòng ban với ứng viên.',
            'interview_date.required' => 'Thời gian phỏng vấn là bắt buộc.',
            'interview_date.after' => 'Thời gian phỏng vấn phải ở tương lai.',
        ]);

        $this->interviews->scheduleInterview(
            $candidate->id,
            $validated['interview_date'],
            $validated['note'] ?? null,
            (int) $validated['interviewer_id'],
        );

        return redirect()
            ->route('manager.recruitment.candidates.show', $candidate)
            ->with('success', 'Đã tạo lịch phỏng vấn và gửi email mời ứng viên.');
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

        $validated = Interview::normalizedEvaluationPayload($request->validated());
        $this->interviews->applyEvaluation($interview, $request->validated());

        $message = $validated['result'] === 'passed'
            ? 'Đã gửi kết quả phỏng vấn cho Admin duyệt.'
            : 'Cập nhật kết quả phỏng vấn thành công.';

        return redirect()
            ->route('manager.recruitment.index')
            ->with('success', $message);
    }

    private function departmentCandidatesQuery(array $departmentIds)
    {
        return Candidate::query()->whereHas(
            'jobPost',
            fn ($query) => $query->whereIn('department_id', $departmentIds)
        );
    }

    private function ensureManagerCanAccessCandidate(Candidate $candidate, Employee $manager): void
    {
        $candidate->loadMissing('jobPost');
        $departmentIds = $this->managerScope->managedDepartmentIds($manager);
        $jobDepartmentId = $candidate->jobPost?->department_id;

        abort_unless(
            $jobDepartmentId !== null && in_array((int) $jobDepartmentId, $departmentIds, true),
            403
        );
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

    private function departmentInterviewersForCandidate(Candidate $candidate)
    {
        $departmentId = $candidate->jobPost?->department_id;

        if (! $departmentId) {
            return collect();
        }

        return Employee::query()
            ->where('department_id', $departmentId)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'employee_code', 'full_name']);
    }

    private function allowedInterviewerIds(Candidate $candidate): \Illuminate\Support\Collection
    {
        return $this->departmentInterviewersForCandidate($candidate)->pluck('id');
    }
}
