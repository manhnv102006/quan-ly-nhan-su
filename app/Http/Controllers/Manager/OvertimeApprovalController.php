<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Concerns\ResolvesCurrentEmployee;
use App\Http\Controllers\Controller;
use App\Http\Requests\OvertimeRequestRejectRequest;
use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestHistory;
use App\Services\OvertimeApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OvertimeApprovalController extends Controller
{
    use ResolvesCurrentEmployee;

    private const PER_PAGE = 10;

    public function __construct(private readonly OvertimeApprovalService $service)
    {
    }

    public function index(Request $request): View
    {
        $manager = $this->currentManagerOrNull();

        if (! $manager) {
            return view('manager.overtime-requests.index', [
                'managerLinked' => false,
                'overtimeRequests' => OvertimeRequest::query()->whereRaw('0 = 1')->paginate(self::PER_PAGE),
                'stats' => ['pending' => 0, 'approved' => 0, 'rejected' => 0],
                'filters' => [],
                'recentHistories' => collect(),
            ]);
        }

        $filters = $request->only([
            'employee_name',
            'employee_code',
            'status',
            'work_date_from',
            'work_date_to',
        ]);

        $scopedQuery = OvertimeRequest::query()->forManagerApproval($manager);

        $stats = [
            'pending' => (clone $scopedQuery)->where('status', OvertimeRequest::STATUS_PENDING)->count(),
            'approved' => (clone $scopedQuery)->where('status', OvertimeRequest::STATUS_APPROVED)->count(),
            'rejected' => (clone $scopedQuery)->where('status', OvertimeRequest::STATUS_REJECTED)->count(),
        ];

        $overtimeRequests = (clone $scopedQuery)
            ->with(['employee.department', 'employee.position', 'approver'])
            ->filter($filters)
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $recentHistories = OvertimeRequestHistory::query()
            ->whereHas('overtimeRequest', fn ($query) => $query->forManagerApproval($manager))
            ->with(['actor', 'overtimeRequest.employee'])
            ->latest('processed_at')
            ->limit(15)
            ->get();

        return view('manager.overtime-requests.index', [
            'managerLinked' => true,
            'overtimeRequests' => $overtimeRequests,
            'stats' => $stats,
            'filters' => $filters,
            'recentHistories' => $recentHistories,
        ]);
    }

    public function show(OvertimeRequest $overtimeRequest): View|RedirectResponse
    {
        if (! $this->currentManagerOrNull()) {
            return redirect()
                ->route('manager.overtime-requests.index')
                ->with('error', 'Tài khoản quản lý chưa liên kết hồ sơ nhân viên. Vui lòng liên hệ quản trị để được hỗ trợ.');
        }

        $this->authorize('view', $overtimeRequest);

        $overtimeRequest->load(['employee.department', 'employee.position', 'approver', 'histories.actor']);

        return view('manager.overtime-requests.show', compact('overtimeRequest'));
    }

    public function approve(OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $this->authorize('approve', $overtimeRequest);

        try {
            $this->service->approve($overtimeRequest, (int) Auth::id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể phê duyệt đơn tăng ca.');
        }

        return back()->with('success', 'Phê duyệt đơn tăng ca thành công.');
    }

    public function reject(OvertimeRequestRejectRequest $request, OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $this->authorize('reject', $overtimeRequest);

        try {
            $this->service->reject($overtimeRequest, (int) Auth::id(), $request->validated('reject_reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể từ chối đơn tăng ca.');
        }

        return back()->with('success', 'Từ chối đơn tăng ca thành công.');
    }
}
