<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EarlyLeaveRequest;
use App\Models\EarlyLeaveRequestHistory;
use App\Services\EarlyLeaveApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EarlyLeaveController extends Controller
{
    public function __construct(private readonly EarlyLeaveApprovalService $approvalService)
    {
    }

    public function index(Request $request): View
    {
        $status = $request->get('status');

        $query = EarlyLeaveRequest::with(['employee.department', 'employee.user.role', 'approver', 'rejecter'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(15)->withQueryString();

        $recentHistories = EarlyLeaveRequestHistory::query()
            ->with(['actor', 'earlyLeaveRequest.employee'])
            ->latest('processed_at')
            ->latest('id')
            ->limit(20)
            ->get();

        return view('admin.early-leave.index', [
            'requests' => $requests,
            'recentHistories' => $recentHistories,
        ]);
    }

    public function approve(EarlyLeaveRequest $earlyLeaveRequest): RedirectResponse
    {
        $this->authorize('approve', $earlyLeaveRequest);

        try {
            $this->approvalService->approve($earlyLeaveRequest, (int) Auth::id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể duyệt đơn về sớm.');
        }

        return back()->with('success', 'Đã duyệt đơn về sớm thành công.');
    }

    public function reject(Request $request, EarlyLeaveRequest $earlyLeaveRequest): RedirectResponse
    {
        $this->authorize('reject', $earlyLeaveRequest);

        $request->validate([
            'reject_reason' => 'required|string|max:500',
        ]);

        try {
            $this->approvalService->reject($earlyLeaveRequest, (int) Auth::id(), null, $request->reject_reason);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể từ chối đơn về sớm.');
        }

        return back()->with('success', 'Đã từ chối đơn về sớm.');
    }
}
