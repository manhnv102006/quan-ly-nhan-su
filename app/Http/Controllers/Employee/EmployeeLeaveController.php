<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\AutoNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeeLeaveController extends Controller
{
    public function __construct(
        private AutoNotificationService $autoNotifications,
    ) {}

    private function getEmployee()
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        if (!$employee) {
            abort(403, 'Tài khoản của bạn chưa được liên kết với hồ sơ nhân viên.');
        }
        return $employee;
    }

    public function index()
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $employee = $this->getEmployee();

        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->with(['approver', 'rejecter'])
            ->latest()
            ->paginate(10);

        return view('employee.leave-requests.index', compact('leaveRequests'));
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
        $this->getEmployee();

        return view('employee.leave-requests.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', LeaveRequest::class);

        $employee = $this->getEmployee();

        $request->validate([
            'leave_type' => 'required|in:annual,sick,unpaid',
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
        $totalDays = $start->diffInDays($end) + 1;

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'reject_reason' => null,
        ]);

        $this->autoNotifications->leaveSubmitted($leaveRequest);

        return redirect()
            ->route('employee.leave-requests')
            ->with('success', 'Tạo đơn xin nghỉ phép thành công.');
    }
}
