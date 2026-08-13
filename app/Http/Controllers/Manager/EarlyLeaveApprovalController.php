<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Concerns\ResolvesCurrentEmployee;
use App\Http\Controllers\Controller;
use App\Models\EarlyLeaveRequest;
use App\Models\EarlyLeaveRequestHistory;
use App\Models\Employee;
use App\Services\EarlyLeaveApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EarlyLeaveApprovalController extends Controller
{
    use ResolvesCurrentEmployee;

    public function __construct(private readonly EarlyLeaveApprovalService $approvalService)
    {
    }

    public function index(Request $request): View
    {
        $manager = $this->currentManagerOrNull();

        $query = EarlyLeaveRequest::with(['employee.department', 'employee.position', 'approver', 'rejecter'])
            ->latest('request_date')
            ->latest('id');

        if ($manager) {
            $query->forManagerApproval($manager);
        } else {
            $query->whereRaw('0 = 1');
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(15)->withQueryString();

        $pendingCount = $manager
            ? EarlyLeaveRequest::query()->forManagerApproval($manager)->where('status', 'pending')->count()
            : 0;

        $recentHistories = $manager
            ? EarlyLeaveRequestHistory::query()
                ->whereHas('earlyLeaveRequest', fn ($query) => $query->forManagerApproval($manager))
                ->with(['actor', 'earlyLeaveRequest.employee'])
                ->latest('processed_at')
                ->latest('id')
                ->limit(15)
                ->get()
            : collect();

        return view('manager.early-leave.index', compact('requests', 'pendingCount', 'manager', 'recentHistories'));
    }

    public function show(EarlyLeaveRequest $earlyLeaveRequest): View
    {
        $this->authorize('view', $earlyLeaveRequest);

        $earlyLeaveRequest->load(['employee.department', 'employee.position', 'approver', 'rejecter', 'histories.actor']);

        return view('manager.early-leave.show', compact('earlyLeaveRequest'));
    }

    public function approve(EarlyLeaveRequest $earlyLeaveRequest): RedirectResponse
    {
        $this->authorize('approve', $earlyLeaveRequest);

        $manager = $this->currentManagerOrNull();

        try {
            $this->approvalService->approve($earlyLeaveRequest, (int) Auth::id(), $manager);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể duyệt đơn về sớm.');
        }

        return back()->with('success', 'Đã duyệt đơn xin về sớm.');
    }

    public function reject(Request $request, EarlyLeaveRequest $earlyLeaveRequest): RedirectResponse
    {
        $this->authorize('reject', $earlyLeaveRequest);

        $request->validate([
            'reject_reason' => ['required', 'string', 'max:500'],
        ], [
            'reject_reason.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        $manager = $this->currentManagerOrNull();

        try {
            $this->approvalService->reject($earlyLeaveRequest, (int) Auth::id(), $manager, $request->reject_reason);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể từ chối đơn về sớm.');
        }

        return back()->with('success', 'Đã từ chối đơn xin về sớm.');
    }
}
