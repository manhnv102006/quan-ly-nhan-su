<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OvertimeRequestRejectRequest;
use App\Http\Requests\OvertimeRequestStoreRequest;
use App\Http\Requests\OvertimeRequestUpdateRequest;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Services\OvertimeApprovalService;
use App\Services\OvertimeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OvertimeRequestController extends Controller
{
    public function __construct(
        private readonly OvertimeRequestService $service,
        private readonly OvertimeApprovalService $approvalService,
    ) {
        $this->authorizeResource(OvertimeRequest::class, 'overtimeRequest');
    }

    public function index(): View
    {
        $overtimeRequests = OvertimeRequest::with(['employee', 'approver'])
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => OvertimeRequest::count(),
            'pending' => OvertimeRequest::where('status', OvertimeRequest::STATUS_PENDING)->count(),
            'approved' => OvertimeRequest::where('status', OvertimeRequest::STATUS_APPROVED)->count(),
            'rejected' => OvertimeRequest::where('status', OvertimeRequest::STATUS_REJECTED)->count(),
            'completed' => OvertimeRequest::where('status', OvertimeRequest::STATUS_COMPLETED)->count(),
        ];

        return view('admin.overtime-requests.index', compact('overtimeRequests', 'stats'));
    }

    public function create(): View
    {
        return view('admin.overtime-requests.create', [
            'employees' => $this->activeEmployees(),
        ]);
    }

    public function store(OvertimeRequestStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['employee_id'] = $data['employee_id'] ?? $request->user()?->employee?->id;

        if (! $data['employee_id']) {
            return back()
                ->withErrors(['employee_id' => 'Không xác định được nhân viên tạo đơn.'])
                ->withInput();
        }

        $this->service->create($data);

        return redirect()
            ->route('admin.overtime-requests.index')
            ->with('success', 'Tạo yêu cầu tăng ca thành công.');
    }

    public function show(OvertimeRequest $overtimeRequest): View
    {
        $overtimeRequest->load(['employee.department', 'approver', 'histories.actor']);

        return view('admin.overtime-requests.show', compact('overtimeRequest'));
    }

    public function edit(OvertimeRequest $overtimeRequest): View
    {
        $this->assertPendingOrAbort($overtimeRequest, 'Chỉ được chỉnh sửa đơn ở trạng thái Pending.');

        return view('admin.overtime-requests.edit', [
            'overtimeRequest' => $overtimeRequest,
            'employees' => $this->activeEmployees(),
        ]);
    }

    public function update(OvertimeRequestUpdateRequest $request, OvertimeRequest $overtimeRequest): RedirectResponse
    {
        if (! $overtimeRequest->isPending()) {
            return redirect()
                ->route('admin.overtime-requests.show', $overtimeRequest)
                ->with('error', 'Đơn đã duyệt/từ chối, không thể chỉnh sửa.');
        }

        $this->service->update($overtimeRequest, $request->validated());

        return redirect()
            ->route('admin.overtime-requests.show', $overtimeRequest)
            ->with('success', 'Cập nhật yêu cầu tăng ca thành công.');
    }

    public function destroy(OvertimeRequest $overtimeRequest): RedirectResponse
    {
        if (! $overtimeRequest->isPending()) {
            return redirect()
                ->route('admin.overtime-requests.show', $overtimeRequest)
                ->with('error', 'Đơn đã duyệt/từ chối, không thể xóa.');
        }

        $overtimeRequest->delete();

        return redirect()
            ->route('admin.overtime-requests.index')
            ->with('success', 'Xóa yêu cầu tăng ca thành công.');
    }

    public function approve(OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $this->authorize('approve', $overtimeRequest);

        try {
            $this->approvalService->approve($overtimeRequest, (int) Auth::id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể phê duyệt đơn tăng ca.');
        }

        return back()->with('success', 'Phê duyệt đơn tăng ca thành công.');
    }

    public function reject(OvertimeRequestRejectRequest $request, OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $this->authorize('reject', $overtimeRequest);

        try {
            $this->approvalService->reject($overtimeRequest, (int) Auth::id(), $request->validated('reject_reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Không thể từ chối đơn tăng ca.');
        }

        return back()->with('success', 'Từ chối đơn tăng ca thành công.');
    }

    private function activeEmployees()
    {
        return Employee::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();
    }

    private function assertPendingOrAbort(OvertimeRequest $overtimeRequest, string $message): void
    {
        if (! $overtimeRequest->isPending()) {
            abort(403, $message);
        }
    }
}
