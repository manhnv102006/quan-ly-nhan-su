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

    public function index(): View
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();

        $overtimeRequests = OvertimeRequest::query()
            ->where('employee_id', $employee->id)
            ->latest('work_date')
            ->latest('id')
            ->paginate(10);

        return view('employee.overtime.index', compact('overtimeRequests'));
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

        $this->overtimeRequests->create([
            'employee_id' => $employee->id,
            'work_date' => $validated['work_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'rate_multiplier' => $validated['rate_multiplier'],
            'reason' => $validated['reason'],
        ]);

        return redirect()
            ->route('employee.overtime-requests')
            ->with('success', 'Đã gửi đơn tăng ca. Vui lòng chờ quản lý phê duyệt.');
    }
}
