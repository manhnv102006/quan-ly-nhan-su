<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OvertimeController extends Controller
{
    public function index(): View
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();

        $overtimeRequests = OvertimeRequest::where('employee_id', $employee->id)
            ->latest('overtime_date')
            ->paginate(10);

        return view('employee.overtime.index', compact('overtimeRequests'));
    }
    public function create(Request $request): View
    {
        $prefill = [
            'overtime_date' => $request->query('date'),
            'start_time' => $request->query('start_time'),
            'end_time' => $request->query('end_time'),
        ];

        return view('employee.overtime.create', compact('prefill'));
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'overtime_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        OvertimeRequest::create([
            'employee_id' => $employee->id,
            'overtime_date' => $validated['overtime_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()
            ->route('employee.overtime-requests')
            ->with('success', 'Đã gửi đơn tăng ca, vui lòng chờ phê duyệt.');
    }
}