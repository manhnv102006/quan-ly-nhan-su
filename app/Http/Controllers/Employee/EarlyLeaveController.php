<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EarlyLeaveRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EarlyLeaveController extends Controller
{
    public function index(): View
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();

        $requests = EarlyLeaveRequest::where('employee_id', $employee->id)
            ->latest('request_date')
            ->latest('id')
            ->paginate(10);

        return view('employee.early-leave.index', compact('requests'));
    }

    public function create(): View
    {
        return view('employee.early-leave.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'request_date' => ['required', 'date', 'after_or_equal:today'],
            'leave_time'   => ['required', 'date_format:H:i'],
            'reason'       => ['required', 'string', 'max:500'],
        ], [
            'request_date.required'     => 'Vui lòng chọn ngày xin về sớm.',
            'request_date.after_or_equal' => 'Ngày xin về sớm phải từ hôm nay trở đi.',
            'leave_time.required'       => 'Vui lòng chọn giờ muốn về sớm.',
            'leave_time.date_format'    => 'Giờ không hợp lệ.',
            'reason.required'           => 'Vui lòng nhập lý do.',
        ]);

        $employee = Employee::where('user_id', Auth::id())->firstOrFail();

        EarlyLeaveRequest::create([
            'employee_id'  => $employee->id,
            'request_date' => $validated['request_date'],
            'leave_time'   => $validated['leave_time'],
            'reason'       => $validated['reason'],
            'status'       => EarlyLeaveRequest::STATUS_PENDING,
        ]);

        return redirect()
            ->route('employee.early-leave.index')
            ->with('success', 'Đã gửi đơn xin về sớm. Vui lòng chờ quản lý phê duyệt.');
    }
}
