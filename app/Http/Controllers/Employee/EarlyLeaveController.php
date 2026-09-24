<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeEarlyLeaveRequest;
use App\Models\EarlyLeaveRequest;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Services\EarlyLeaveApprovalService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EarlyLeaveController extends Controller
{
    public function __construct(private readonly EarlyLeaveApprovalService $approvalService)
    {
    }

    public function index(Request $request): View
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        $filter = (string) $request->query('filter', 'all');
        if (! in_array($filter, ['all', 'active', 'history'], true)) {
            $filter = 'all';
        }

        $baseQuery = EarlyLeaveRequest::query()->where('employee_id', $employee->id);
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('status', EarlyLeaveRequest::STATUS_PENDING)->count(),
            'history' => (clone $baseQuery)->whereIn('status', [EarlyLeaveRequest::STATUS_APPROVED, EarlyLeaveRequest::STATUS_REJECTED])->count(),
        ];

        $requests = (clone $baseQuery)
            ->employeeListFilter($filter)
            ->latest('request_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('employee.early-leave.index', compact('requests', 'filter', 'stats'));
    }

    public function show(EarlyLeaveRequest $earlyLeaveRequest): View
    {
        $this->authorize('view', $earlyLeaveRequest);

        $earlyLeaveRequest->load(['approver', 'rejecter', 'histories.actor']);

        return view('employee.early-leave.show', compact('earlyLeaveRequest'));
    }

    public function create(): View
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();

        $recentRequests = EarlyLeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->latest('request_date')
            ->latest('id')
            ->limit(5)
            ->get();

        $stats = [
            'pending'  => EarlyLeaveRequest::where('employee_id', $employee->id)->where('status', EarlyLeaveRequest::STATUS_PENDING)->count(),
            'approved' => EarlyLeaveRequest::where('employee_id', $employee->id)->where('status', EarlyLeaveRequest::STATUS_APPROVED)->count(),
            'rejected' => EarlyLeaveRequest::where('employee_id', $employee->id)->where('status', EarlyLeaveRequest::STATUS_REJECTED)->count(),
        ];

        $shiftSchedule = EmployeeShift::query()
            ->where('employee_id', $employee->id)
            ->whereDate('work_date', '>=', today())
            ->with('shift')
            ->orderBy('work_date')
            ->get()
            ->groupBy(fn (EmployeeShift $row) => $row->work_date->format('Y-m-d'))
            ->map(fn ($rows) => $rows->map(function (EmployeeShift $row) {
                $shift = $row->shift;
                if (! $shift) {
                    return null;
                }

                return [
                    'name'  => $shift->shift_name,
                    'start' => Carbon::parse($shift->start_time)->format('H:i'),
                    'end'   => Carbon::parse($shift->end_time)->format('H:i'),
                ];
            })->filter()->values()->all());

        return view('employee.early-leave.create', compact('recentRequests', 'stats', 'shiftSchedule'));
    }

    public function store(StoreEmployeeEarlyLeaveRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();

        $earlyLeaveRequest = EarlyLeaveRequest::create([
            'employee_id'  => $employee->id,
            'request_date' => $validated['request_date'],
            'leave_time'   => $validated['leave_time'],
            'reason'       => $validated['reason'],
            'status'       => EarlyLeaveRequest::STATUS_PENDING,
        ]);

        $this->approvalService->logSubmitted($earlyLeaveRequest, (int) Auth::id());

        return redirect()
            ->route('employee.early-leave.index')
            ->with('success', 'Đã gửi đơn xin về sớm. Vui lòng chờ quản lý phê duyệt.');
    }
}
