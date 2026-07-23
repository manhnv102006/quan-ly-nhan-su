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
            'pending' => (clone $scopedQuery)->awaitingManagerApproval()->count(),
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

    public function bulkApprove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'overtime_request_ids' => ['required', 'array', 'min:1'],
            'overtime_request_ids.*' => ['integer'],
        ]);

        $manager = $this->currentManagerOrNull();

        if (! $manager) {
            return back()->with('error', 'Tài khoản quản lý chưa liên kết hồ sơ nhân viên. Vui lòng liên hệ quản trị để được hỗ trợ.');
        }

        $overtimeRequests = OvertimeRequest::query()
            ->forManagerApproval($manager)
            ->awaitingManagerApproval()
            ->whereIn('id', $validated['overtime_request_ids'])
            ->get();

        if ($overtimeRequests->isEmpty()) {
            return back()->with('error', 'Không có đơn hợp lệ để duyệt.');
        }

        foreach ($overtimeRequests as $overtimeRequest) {
            $this->authorize('approve', $overtimeRequest);
        }

        $result = $this->service->bulkApprove($overtimeRequests, (int) Auth::id());

        if ($result['approved'] === 0) {
            return back()->with('error', 'Không thể duyệt các đơn đã chọn.');
        }

        $message = 'Đã duyệt thành công '.$result['approved'].' đơn tăng ca.';

        if ($result['failed'] > 0) {
            $message .= ' '.$result['failed'].' đơn không thể duyệt.';
        }

        return back()->with('success', $message);
    }

    public function bulkReject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'overtime_request_ids' => ['required', 'array', 'min:1'],
            'overtime_request_ids.*' => ['integer'],
            'reject_reason' => ['required', 'string', 'min:1', 'max:1000'],
        ], [
            'reject_reason.required' => 'Vui lòng nhập lý do từ chối.',
            'reject_reason.min' => 'Vui lòng nhập lý do từ chối.',
            'reject_reason.max' => 'Lý do từ chối không được vượt quá 1000 ký tự.',
        ]);

        $manager = $this->currentManagerOrNull();

        if (! $manager) {
            return back()->with('error', 'Tài khoản quản lý chưa liên kết hồ sơ nhân viên. Vui lòng liên hệ quản trị để được hỗ trợ.');
        }

        $overtimeRequests = OvertimeRequest::query()
            ->forManagerApproval($manager)
            ->awaitingManagerApproval()
            ->whereIn('id', $validated['overtime_request_ids'])
            ->get();

        if ($overtimeRequests->isEmpty()) {
            return back()->with('error', 'Không có đơn hợp lệ để từ chối.');
        }

        foreach ($overtimeRequests as $overtimeRequest) {
            $this->authorize('reject', $overtimeRequest);
        }

        $result = $this->service->bulkReject(
            $overtimeRequests,
            (int) Auth::id(),
            trim($validated['reject_reason'])
        );

        if ($result['rejected'] === 0) {
            return back()
                ->withInput()
                ->with('error', 'Không thể từ chối các đơn đã chọn.');
        }

        $message = 'Đã từ chối thành công '.$result['rejected'].' đơn tăng ca.';

        if ($result['failed'] > 0) {
            $message .= ' '.$result['failed'].' đơn không thể từ chối.';
        }

        return back()->with('success', $message);
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
