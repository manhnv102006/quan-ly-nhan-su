<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\AutoNotificationService;
use App\Services\DepartmentLeaveCapacityService;
use App\Services\LeaveApprovalService;
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

        return view('employee.leave-requests.index', compact('leaveRequests', 'isManager', 'filter', 'stats'));
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $this->authorize('view', $leaveRequest);

        $leaveRequest->load(['approver', 'rejecter', 'histories.actor']);

        return view('employee.leave-requests.show', compact('leaveRequest'));
    }

    public function create()
    {
        $this->authorize('create', LeaveRequest::class);
        $employee = $this->getEmployee();

        return view('employee.leave-requests.create', [
            'leaveCapacityPercent' => $employee->leaveCapacityPercent(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', LeaveRequest::class);

        $employee = $this->getEmployee();

        $request->validate([
            'leave_type' => ['required', Rule::in(LeaveRequest::selectableLeaveTypes())],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ], [
            'leave_type.required' => 'Vui lòng chọn loại nghỉ phép.',
            'leave_type.in' => 'Loại nghỉ phép không hợp lệ.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'start_date.date' => 'Ngày bắt đầu không đúng định dạng.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc.',
            'end_date.date' => 'Ngày kết thúc không đúng định dạng.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'reason.required' => 'Vui lòng nhập lý do xin nghỉ phép.',
            'reason.max' => 'Lý do xin nghỉ không được vượt quá 1000 ký tự.',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);

        $holidays = \App\Models\Holiday::inRange($start->format('Y-m-d'), $end->format('Y-m-d'))->get();

        if ($request->leave_type === 'half_day') {
            if (! $start->isSameDay($end)) {
                return back()->withErrors([
                    'end_date' => 'Nghỉ nửa ngày chỉ áp dụng trong một ngày. Ngày bắt đầu và kết thúc phải trùng nhau.',
                ])->withInput();
            }

            if ($start->isSunday()) {
                return back()->withErrors(['start_date' => 'Không thể xin nghỉ nửa ngày vào Chủ nhật.'])->withInput();
            }

            $isHoliday = $holidays->contains(function ($holiday) use ($start) {
                return $start->between($holiday->start_date, $holiday->end_date);
            });

            if ($isHoliday) {
                return back()->withErrors(['start_date' => 'Không thể xin nghỉ nửa ngày vào ngày Lễ.'])->withInput();
            }

            $totalDays = 0.5;
        } else {
            $totalDays = 0;
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                if ($date->isSunday()) {
                    continue;
                }

                $isHoliday = $holidays->contains(function ($holiday) use ($date) {
                    return $date->between($holiday->start_date, $holiday->end_date);
                });

                if ($isHoliday) {
                    continue;
                }

                $totalDays++;
            }

            if ($totalDays === 0) {
                return back()->withErrors(['start_date' => 'Khoảng thời gian bạn chọn toàn bộ là ngày nghỉ/ngày Lễ. Vui lòng chọn lại.'])->withInput();
            }
        }

        return DB::transaction(function () use ($employee, $request, $totalDays) {
            Employee::query()->whereKey($employee->id)->lockForUpdate()->first();
            $this->departmentLeaveCapacity->lockDepartment($employee->department_id);

            $duplicatePending = LeaveRequest::query()
                ->where('employee_id', $employee->id)
                ->where('status', LeaveRequest::STATUS_PENDING)
                ->where('leave_type', $request->leave_type)
                ->whereDate('start_date', $request->start_date)
                ->whereDate('end_date', $request->end_date)
                ->exists();

            if ($duplicatePending) {
                return redirect()
                    ->route('employee.leave-requests')
                    ->with('success', 'Tạo đơn xin nghỉ phép thành công.');
            }

            $overlap = LeaveRequest::where('employee_id', $employee->id)
                ->where('status', LeaveRequest::STATUS_APPROVED)
                ->overlappingPeriod($request->start_date, $request->end_date)
                ->exists();

            if ($overlap) {
                return back()->withErrors([
                    'start_date' => 'Khoảng nghỉ '.(\App\Support\LeaveDateRange::formatPeriod($request->start_date, $request->end_date)).' trùng với đơn nghỉ phép đã duyệt khác.',
                ])->withInput();
            }

            $capacityError = $this->departmentLeaveCapacity->submitBlockedMessage(
                $employee,
                $request->start_date,
                $request->end_date,
                $totalDays,
            );

            if ($capacityError !== null) {
                return back()->withErrors(['leave_capacity' => $capacityError])->withInput();
            }

            $leaveRequest = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type' => $request->leave_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_days' => $totalDays,
                'reason' => $request->reason,
                'status' => LeaveRequest::STATUS_PENDING,
                'approved_by' => null,
                'approved_at' => null,
                'reject_reason' => null,
            ]);

            $this->leaveApprovalService->logSubmitted($leaveRequest, (int) Auth::id());
            $this->autoNotifications->leaveSubmitted($leaveRequest);

            return redirect()
                ->route('employee.leave-requests')
                ->with('success', 'Tạo đơn xin nghỉ phép thành công.');
        });
    }
}
