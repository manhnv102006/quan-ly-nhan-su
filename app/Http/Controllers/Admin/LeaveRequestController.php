<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(): View
    {
        $leaveRequests = LeaveRequest::with([
            'employee.department'
        ])
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => LeaveRequest::count(),
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
        ];

        return view(
            'admin.leave-requests.index',
            compact(
                'leaveRequests',
                'stats'
            )
        );
    }

    public function show(
        LeaveRequest $leaveRequest
    ): View {
        $leaveRequest->load([
            'employee.department',
            'employee.position',
            'approver'
        ]);

        return view(
            'admin.leave-requests.show',
            compact('leaveRequest')
        );
    }

    public function approve(
        LeaveRequest $leaveRequest
    ): RedirectResponse {

        $employee = Employee::where(
            'user_id',
            Auth::id()
        )->first();

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $employee?->id,
        ]);

        return redirect()
            ->route(
                'admin.leave-requests.show',
                $leaveRequest
            )
            ->with(
                'success',
                'Đã duyệt đơn nghỉ phép thành công.'
            );
    }

    public function reject(
        LeaveRequest $leaveRequest
    ): RedirectResponse {

        $employee = Employee::where(
            'user_id',
            Auth::id()
        )->first();

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => $employee?->id,
        ]);

        return redirect()
            ->route(
                'admin.leave-requests.show',
                $leaveRequest
            )
            ->with(
                'success',
                'Đã từ chối đơn nghỉ phép.'
            );
    }
}