<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Concerns\ResolvesCurrentEmployee;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRequestRejectRequest;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use App\Services\LeaveApprovalService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LeaveApprovalController extends Controller
{
    use ResolvesCurrentEmployee;

    public function __construct(private readonly LeaveApprovalService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAnyAsManager', LeaveRequest::class);

        $manager = $this->currentManagerOrNull();

        if (! $manager) {
            return view('manager.leave-requests.index', [
                'managerLinked' => false,
                'leaveRequests' => LeaveRequest::query()->whereRaw('0 = 1')->paginate(10),
                'stats' => ['pending' => 0, 'approved' => 0, 'rejected' => 0],
                'myLeaveStats' => null,
                'filters' => [],
                'recentHistories' => collect(),
            ]);
        }

        $filters = $request->only(['employee_name', 'employee_code', 'leave_type', 'status', 'start_from', 'start_to']);

        $scopedQuery = $this->managerScopedQuery($manager);

        $stats = [
            'pending' => (clone $scopedQuery)->awaitingManagerApproval()->count(),
            'approved' => (clone $scopedQuery)->where('status', LeaveRequest::STATUS_APPROVED)->count(),
            'rejected' => (clone $scopedQuery)->where('status', LeaveRequest::STATUS_REJECTED)->count(),
        ];

        $myLeaveQuery = LeaveRequest::query()->where('employee_id', $manager->id);
        $myLeaveStats = [
            'total' => (clone $myLeaveQuery)->count(),
            'pending' => (clone $myLeaveQuery)->where('status', LeaveRequest::STATUS_PENDING)->count(),
        ];

        $leaveRequests = (clone $scopedQuery)
            ->with(['employee.department', 'employee.position', 'approver', 'rejecter'])
            ->filter($filters)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $recentHistories = LeaveRequestHistory::query()
            ->whereHas('leaveRequest', function ($query) use ($manager) {
                $query->forManager($manager)
                    ->whereHas('employee', function ($employeeQuery) {
                        $employeeQuery->whereDoesntHave('user', function ($userQuery) {
                            $userQuery->whereHas('role', fn ($roleQuery) => $roleQuery->where('name', 'manager'));
                        });
                    });
            })
            ->with(['actor', 'leaveRequest.employee'])
            ->latest()
            ->limit(15)
            ->get();

        return view('manager.leave-requests.index', [
            'managerLinked' => true,
            'leaveRequests' => $leaveRequests,
            'stats' => $stats,
            'myLeaveStats' => $myLeaveStats,
            'filters' => $filters,
            'recentHistories' => $recentHistories,
        ]);
    }

    public function show(LeaveRequest $leaveRequest): View|RedirectResponse
    {
        if (! $this->currentManagerOrNull()) {
            return redirect()
                ->route('manager.leave-requests.index')
                ->with('error', 'Tài khoản quản lý chưa liên kết hồ sơ nhân viên. Vui lòng liên hệ quản trị để được hỗ trợ.');
        }

        $this->authorize('viewAsManager', $leaveRequest);

        $leaveRequest->load(['employee.department', 'employee.position', 'approver', 'rejecter', 'histories.actor']);

        return view('manager.leave-requests.show', compact('leaveRequest'));
    }

    public function approve(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('approve', $leaveRequest);

        $manager = $this->currentManager();

        try {
            $this->service->approve($leaveRequest, (int) Auth::id(), $manager);
        } catch (ValidationException $e) {
            $reason = collect($e->errors())->flatten()->first() ?? 'Lỗi không xác định.';
            return redirect()
                ->route('manager.leave-requests.show', $leaveRequest)
                ->withErrors($e->errors())
                ->with('error', 'Không thể duyệt đơn: ' . $reason);
        }

        return redirect()
            ->route('manager.leave-requests.index')
            ->with('success', 'Đã duyệt đơn nghỉ phép thành công.');
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'leave_request_ids' => ['required', 'array', 'min:1'],
            'leave_request_ids.*' => ['integer'],
        ]);

        $manager = $this->currentManagerOrNull();

        if (! $manager) {
            return back()->with('error', 'Tài khoản quản lý chưa liên kết hồ sơ nhân viên. Vui lòng liên hệ quản trị để được hỗ trợ.');
        }

        $leaveRequests = $this->resolveSelectedRequests($manager, $validated['leave_request_ids']);

        if ($leaveRequests->isEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Không có đơn hợp lệ để duyệt.');
        }

        foreach ($leaveRequests as $leaveRequest) {
            $this->authorize('approve', $leaveRequest);
        }

        $result = $this->service->bulkApprove($leaveRequests, (int) Auth::id(), $manager);

        if ($result['approved'] === 0) {
            return back()
                ->withInput()
                ->with('error', 'Không thể duyệt các đơn đã chọn.');
        }

        $message = 'Đã duyệt thành công '.$result['approved'].' đơn nghỉ phép.';

        if ($result['failed'] > 0) {
            $message .= ' '.$result['failed'].' đơn không thể duyệt.';
        }

        return back()->with('success', $message);
    }

    public function bulkReject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'leave_request_ids' => ['required', 'array', 'min:1'],
            'leave_request_ids.*' => ['integer'],
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

        $leaveRequests = $this->resolveSelectedRequests($manager, $validated['leave_request_ids']);

        if ($leaveRequests->isEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Không có đơn hợp lệ để từ chối.');
        }

        foreach ($leaveRequests as $leaveRequest) {
            $this->authorize('reject', $leaveRequest);
        }

        $result = $this->service->bulkReject(
            $leaveRequests,
            (int) Auth::id(),
            $manager,
            trim($validated['reject_reason'])
        );

        if ($result['rejected'] === 0) {
            return back()
                ->withInput()
                ->with('error', 'Không thể từ chối các đơn đã chọn.');
        }

        $message = 'Đã từ chối thành công '.$result['rejected'].' đơn nghỉ phép.';

        if ($result['failed'] > 0) {
            $message .= ' '.$result['failed'].' đơn không thể từ chối.';
        }

        return back()->with('success', $message);
    }

    public function reject(LeaveRequestRejectRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('reject', $leaveRequest);

        $manager = $this->currentManager();

        try {
            $this->service->reject($leaveRequest, (int) Auth::id(), $manager, $request->validated('reject_reason'));
        } catch (ValidationException $e) {
            $reason = collect($e->errors())->flatten()->first() ?? 'Lỗi không xác định.';
            return redirect()
                ->route('manager.leave-requests.show', $leaveRequest)
                ->withErrors($e->errors())
                ->with('error', 'Không thể từ chối đơn: ' . $reason);
        }

        return redirect()
            ->route('manager.leave-requests.index')
            ->with('success', 'Đã từ chối đơn nghỉ phép thành công.');
    }

    /**
     * @return Builder<LeaveRequest>
     */
    private function managerScopedQuery(Employee $manager): Builder
    {
        return LeaveRequest::query()
            ->forManager($manager)
            ->whereHas('employee', function ($query) {
                $query->whereDoesntHave('user', function ($userQuery) {
                    $userQuery->whereHas('role', fn ($roleQuery) => $roleQuery->where('name', 'manager'));
                });
            });
    }

    /**
     * @param  array<int>  $ids
     * @return Collection<int, LeaveRequest>
     */
    private function resolveSelectedRequests(Employee $manager, array $ids): Collection
    {
        return $this->managerScopedQuery($manager)
            ->awaitingManagerApproval()
            ->whereIn('id', $ids)
            ->get()
            ->filter(fn (LeaveRequest $leaveRequest) => $leaveRequest->isAwaitingManagerApproval())
            ->values();
    }
}
