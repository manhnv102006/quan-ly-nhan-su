<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeOvertimeRequest;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Services\OvertimeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OvertimeController extends Controller
{
    public function __construct(private readonly OvertimeRequestService $overtimeRequests)
    {
    }

    public function index(Request $request): View
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        $filter = (string) $request->query('filter', 'all');
        if (! in_array($filter, ['all', 'active', 'history'], true)) {
            $filter = 'all';
        }

        $baseQuery = OvertimeRequest::query()->where('employee_id', $employee->id);
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('status', OvertimeRequest::STATUS_PENDING)->count(),
            'history' => (clone $baseQuery)->whereIn('status', [
                OvertimeRequest::STATUS_APPROVED,
                OvertimeRequest::STATUS_REJECTED,
                OvertimeRequest::STATUS_COMPLETED,
            ])->count(),
        ];

        $overtimeRequests = (clone $baseQuery)
            ->employeeListFilter($filter)
            ->latest('work_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('employee.overtime.index', compact('overtimeRequests', 'filter', 'stats'));
    }

    public function show(OvertimeRequest $overtimeRequest): View
    {
        $this->authorize('view', $overtimeRequest);

        $overtimeRequest->load(['approver', 'histories.actor']);

        return view('employee.overtime.show', compact('overtimeRequest'));
    }

    public function create(Request $request): View
    {
        $prefill = [
            'work_date' => $request->query('work_date', $request->query('date')),
            'start_time' => $request->query('start_time'),
            'end_time' => $request->query('end_time'),
        ];

        return view('employee.overtime.create', compact('prefill'));
    }

    public function store(StoreEmployeeOvertimeRequest $request): RedirectResponse
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        $validated = $request->validated();

        $overtimeRequest = $this->overtimeRequests->create([
            'employee_id' => $employee->id,
            'work_date' => $validated['work_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'rate_multiplier' => $validated['rate_multiplier'],
            'reason' => $validated['reason'],
        ]);

        $this->overtimeRequests->logSubmitted($overtimeRequest, (int) Auth::id());

        return redirect()
            ->route('employee.overtime-requests')
            ->with('success', 'Đã gửi đơn tăng ca. Vui lòng chờ quản lý phê duyệt.');
    }
}
