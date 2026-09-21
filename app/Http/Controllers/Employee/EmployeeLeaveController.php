<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\AutoNotificationService;
use App\Services\DepartmentLeaveCapacityService;
use App\Services\LeaveApprovalService;
use App\Services\LeaveBalanceService;
use App\Services\LeaveRequestDocumentService;
use App\Services\LeaveTypeQuotaService;
use App\Support\LeaveDocumentRules;
use App\Support\LeaveTypeRegistry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeLeaveController extends Controller
{
    public function __construct(
        private AutoNotificationService $autoNotifications,
        private DepartmentLeaveCapacityService $departmentLeaveCapacity,
        private LeaveApprovalService $leaveApprovalService,
        private LeaveBalanceService $leaveBalanceService,
        private LeaveRequestDocumentService $leaveRequestDocumentService,
        private LeaveTypeQuotaService $leaveTypeQuota,
    ) {}

    private function getEmployee()
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        if (!$employee) {
            abort(403, 'Tài khoản của bạn chưa được liên kết với hồ sơ nhân viên.');
        }
        return $employee;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $employee = $this->getEmployee();
        $filter = (string) $request->query('filter', 'all');
        if (! in_array($filter, ['all', 'active', 'history'], true)) {
            $filter = 'all';
        }

        $baseQuery = LeaveRequest::query()->where('employee_id', $employee->id);
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('status', LeaveRequest::STATUS_PENDING)->count(),
            'history' => (clone $baseQuery)->whereIn('status', [LeaveRequest::STATUS_APPROVED, LeaveRequest::STATUS_REJECTED])->count(),
        ];

        $leaveRequests = (clone $baseQuery)
            ->with(['approver', 'rejecter'])
            ->employeeListFilter($filter)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $isManager = Auth::user()->role?->name === 'manager';
        $leaveBalance = $this->leaveBalanceService->forEmployee($employee);

        return view('employee.leave-requests.index', compact('leaveRequests', 'isManager', 'filter', 'stats', 'leaveBalance'));
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $this->authorize('view', $leaveRequest);

        $leaveRequest->load(['approver', 'rejecter', 'histories.actor', 'document']);

        return view('employee.leave-requests.show', compact('leaveRequest'));
    }

    public function create()
    {
        $this->authorize('create', LeaveRequest::class);
        $employee = $this->getEmployee();

        return view('employee.leave-requests.create', [
            'leaveCapacityPercent' => $employee->leaveCapacityPercent(),
            'leaveBalance' => $this->leaveBalanceService->forEmployee($employee),
            'leaveTypeOptions' => LeaveRequest::leaveTypeLabelsForEmployee($employee),
            'leaveTypesRequiringDocument' => LeaveDocumentRules::typesRequiringDocument(),
            'leaveDocumentHints' => LeaveDocumentRules::documentHints(),
        ]);
    }

    public function downloadDocument(LeaveRequest $leaveRequest)
    {
        $this->authorize('view', $leaveRequest);

        $leaveRequest->loadMissing('document');
        $document = $leaveRequest->document;
        abort_if($document === null, 404);

        $user = Auth::user();
        abort_unless(
            $this->leaveRequestDocumentService->userCanDownload($document, (int) $user?->id, $user?->role?->name),
            403,
        );

        return $this->leaveRequestDocumentService->downloadResponse($document);
    }

    public function store(Request $request)
    {
        $this->authorize('create', LeaveRequest::class);

        $employee = $this->getEmployee();

        $request->validate([
            'leave_type' => ['required', Rule::in(LeaveRequest::selectableLeaveTypesForEmployee($employee))],
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ], [
            'leave_type.required' => 'Vui lòng chọn loại nghỉ phép.',
            'leave_type.in' => 'Loại nghỉ phép không hợp lệ.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'start_date.date' => 'Ngày bắt đầu không đúng định dạng.',
            'start_date.after_or_equal' => 'Không thể xin nghỉ trong quá khứ.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc.',
            'end_date.date' => 'Ngày kết thúc không đúng định dạng.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'reason.required' => 'Vui lòng nhập lý do xin nghỉ phép.',
            'reason.max' => 'Lý do xin nghỉ không được vượt quá 1000 ký tự.',
        ]);

        $request->validate(
            $this->leaveRequestDocumentService->validationRules((string) $request->leave_type),
            $this->leaveRequestDocumentService->validationMessages(),
        );

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);

        $holidays = \App\Models\Holiday::inRange($start->format('Y-m-d'), $end->format('Y-m-d'))->get();

        if ($request->leave_type === 'half_day') {
            $request->validate([
                'half_day_period' => ['required', Rule::in([
                    LeaveRequest::HALF_DAY_MORNING,
                    LeaveRequest::HALF_DAY_AFTERNOON,
                ])],
            ], [
                'half_day_period.required' => 'Vui lòng chọn buổi nghỉ (sáng hoặc chiều).',
                'half_day_period.in' => 'Buổi nghỉ không hợp lệ. Chọn sáng hoặc chiều.',
            ]);

            if (! $start->isSameDay($end)) {
                return back()->withErrors([
                    'end_date' => 'Nghỉ nửa ngày chỉ áp dụng trong một ngày. Ngày bắt đầu và kết thúc phải trùng nhau.',
                ])->withInput();
            }

            if ($start->isSunday()) {
                return back()->withErrors(['start_date' => 'Không thể xin nghỉ nửa ngày vào Chủ nhật.'])->withInput();
            }

            $isHoliday = $holidays->contains(function ($holiday) use ($start) {
                return \App\Support\LeaveDateRange::dayWithinPeriod($start, $holiday->start_date, $holiday->end_date);
            });

            if ($isHoliday) {
                return back()->withErrors(['start_date' => 'Không thể xin nghỉ nửa ngày vào ngày Lễ.'])->withInput();
            }

            $totalDays = 0.5;
        } else {
            $totalDays = count($this->leaveBalanceService->workingDayDatesInRange($start, $end, $holidays));

            if ($totalDays === 0) {
                return back()->withErrors(['start_date' => 'Khoảng thời gian bạn chọn toàn bộ là ngày nghỉ/ngày Lễ. Vui lòng chọn lại.'])->withInput();
            }
        }

        $quotaError = $this->leaveTypeQuota->violationMessage(
            $employee,
            LeaveTypeRegistry::find((string) $request->leave_type),
            $start,
            $totalDays,
        );

        if ($quotaError !== null) {
            return back()->withErrors(['leave_type' => $quotaError])->withInput();
        }

        $submissionSegments = [[
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'half_day_period' => $request->leave_type === 'half_day' ? $request->half_day_period : null,
            'total_days' => $totalDays,
            'reason' => $request->reason,
        ]];
        $splitNotice = null;

        if ($request->leave_type === 'annual') {
            $annualPlan = $this->leaveBalanceService->planAnnualLeaveSubmission(
                $employee,
                $start,
                $end,
                $holidays,
                $totalDays,
            );

            if ($annualPlan['blocked']) {
                return back()->withErrors(['leave_type' => $annualPlan['message']])->withInput();
            }

            $submissionSegments = array_map(function (array $segment) use ($request, $annualPlan) {
                $reason = $request->reason;
                if ($annualPlan['split'] && $segment['leave_type'] === 'unpaid') {
                    $reason .= ' (Phần vượt số dư phép năm — hệ thống tự chuyển sang nghỉ không lương.)';
                }

                return [
                    ...$segment,
                    'reason' => $reason,
                ];
            }, $annualPlan['segments']);

            if ($annualPlan['split']) {
                $splitNotice = sprintf(
                    'Đã tách đơn: %s nghỉ phép và %s nghỉ không lương do vượt số dư phép năm.',
                    $this->formatLeaveDays($annualPlan['paid_days']),
                    $this->formatLeaveDays($annualPlan['unpaid_days']),
                );
            }
        }

        $requiresDocument = LeaveDocumentRules::requiresDocument((string) $request->leave_type);
        $supportingDocument = $request->file('supporting_document');

        return DB::transaction(function () use ($employee, $request, $totalDays, $submissionSegments, $splitNotice, $requiresDocument, $supportingDocument) {
            Employee::query()->whereKey($employee->id)->lockForUpdate()->first();
            $this->departmentLeaveCapacity->lockDepartment($employee->department_id);

            $halfDayPeriod = $request->leave_type === 'half_day' ? $request->half_day_period : null;
            $overlap = LeaveRequest::query()
                ->where('employee_id', $employee->id)
                ->whereIn('status', [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_APPROVED])
                ->overlappingPeriod($request->start_date, $request->end_date)
                ->get()
                ->contains(fn (LeaveRequest $existing) => $existing->conflictsWithSubmission(
                    $request->start_date,
                    $request->end_date,
                    (string) $request->leave_type,
                    $halfDayPeriod,
                ));

            if ($overlap) {
                $overlapMessage = $request->leave_type === 'half_day' && filled($halfDayPeriod)
                    ? 'Buổi nghỉ '.(LeaveRequest::HALF_DAY_PERIOD_LABELS[$halfDayPeriod] ?? $halfDayPeriod).' ngày '.Carbon::parse($request->start_date)->format('d/m/Y').' trùng với đơn nghỉ phép đã duyệt hoặc đang chờ duyệt.'
                    : 'Khoảng nghỉ '.(\App\Support\LeaveDateRange::formatPeriod($request->start_date, $request->end_date)).' trùng với đơn nghỉ phép đã duyệt hoặc đang chờ duyệt.';

                return back()->withErrors([
                    'start_date' => $overlapMessage,
                ])->withInput();
            }

            $capacityError = $this->departmentLeaveCapacity->submitBlockedMessage(
                $employee,
                $request->start_date,
                $request->end_date,
                $totalDays,
                (string) $request->leave_type,
            );

            if ($capacityError !== null) {
                return back()->withErrors(['leave_capacity' => $capacityError])->withInput();
            }

            foreach ($submissionSegments as $segment) {
                $leaveRequest = LeaveRequest::create([
                    'employee_id' => $employee->id,
                    'leave_type' => $segment['leave_type'],
                    'start_date' => $segment['start_date'],
                    'end_date' => $segment['end_date'],
                    'half_day_period' => $segment['half_day_period'] ?? null,
                    'total_days' => $segment['total_days'],
                    'reason' => $segment['reason'],
                    'status' => LeaveRequest::STATUS_PENDING,
                    'approved_by' => null,
                    'approved_at' => null,
                    'reject_reason' => null,
                ]);

                if ($requiresDocument && $segment['leave_type'] === $request->leave_type && $supportingDocument) {
                    $this->leaveRequestDocumentService->store($leaveRequest, $employee, $supportingDocument);
                    $requiresDocument = false;
                }

                $this->leaveApprovalService->logSubmitted($leaveRequest, (int) Auth::id());
                $this->autoNotifications->leaveSubmitted($leaveRequest);
            }

            $successMessage = $splitNotice ?? 'Tạo đơn xin nghỉ phép thành công.';

            return redirect()
                ->route('employee.leave-requests')
                ->with('success', $successMessage);
        });
    }

    private function formatLeaveDays(float $days): string
    {
        if (fmod($days, 1.0) === 0.0) {
            return ((int) $days).' ngày';
        }

        return number_format($days, 1, ',', '').' ngày';
    }
}
