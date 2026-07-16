<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EarlyLeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EarlyLeaveController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status');

        $query = EarlyLeaveRequest::with(['employee.department', 'approver'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(15)->withQueryString();

        return view('admin.early-leave.index', [
            'requests' => $requests,
        ]);
    }

    public function approve(EarlyLeaveRequest $earlyLeaveRequest)
    {
        if ($earlyLeaveRequest->status !== EarlyLeaveRequest::STATUS_PENDING) {
            return back()->with('error', 'Chỉ có thể duyệt đơn ở trạng thái chờ.');
        }

        $earlyLeaveRequest->update([
            'status' => EarlyLeaveRequest::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Đã duyệt đơn về sớm thành công.');
    }

    public function reject(Request $request, EarlyLeaveRequest $earlyLeaveRequest)
    {
        $request->validate([
            'reject_reason' => 'required|string|max:500',
        ]);

        if ($earlyLeaveRequest->status !== EarlyLeaveRequest::STATUS_PENDING) {
            return back()->with('error', 'Chỉ có thể từ chối đơn ở trạng thái chờ.');
        }

        $earlyLeaveRequest->update([
            'status' => EarlyLeaveRequest::STATUS_REJECTED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $request->reject_reason,
        ]);

        return back()->with('success', 'Đã từ chối đơn về sớm.');
    }
}
