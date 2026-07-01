<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OvertimeRequestRejectRequest;
use App\Http\Requests\OvertimeRequestStoreRequest;
use App\Http\Requests\OvertimeRequestUpdateRequest;
use App\Http\Requests\UpdateOvertimeStatusRequest;
use App\Models\Department;
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
        $this->authorizeResource(OvertimeRequest::class, 'overtime_request');
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
        $employees = $this->activeEmployees();

        $departments = Department::query()
            ->where('status', 'active')
            ->withCount([
                'employees as active_employees_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->orderBy('department_name')
            ->get(['id', 'department_code', 'department_name']);

        $companyEmployeeCount = Employee::query()
            ->where('status', 'active')
            ->count();

        return view('admin.overtime-requests.create', compact(
            'employees',
            'departments',
            'companyEmployeeCount',
        ));
    }

    public function store(OvertimeRequestStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $employeeIds = $request->resolveEmployeeIds();

        if ($employeeIds === []) {
            return back()
                ->withInput()
                ->withErrors(['assignment_scope' => 'Không tìm thấy nhân viên phù hợp để tạo đơn tăng ca.']);
        }

        $this->service->createMany($employeeIds, $data);

        $message = match ($data['assignment_scope']) {
            'employee' => 'Tạo yêu cầu tăng ca thành công.',
            'department' => 'Đã tạo '.count($employeeIds).' đơn tăng ca cho nhân viên trong phòng ban.',
            'company' => 'Đã tạo '.count($employeeIds).' đơn tăng ca cho toàn công ty.',
            default => 'Tạo yêu cầu tăng ca thành công.',
        };

        return redirect()
            ->route('admin.overtime-requests.index')
            ->with('success', $message);
    }

    public function show(OvertimeRequest $overtimeRequest): View
    {
        $overtimeRequest->load(['employee.department', 'approver', 'histories.actor']);

        return view('admin.overtime-requests.show', compact('overtimeRequest'));
    }

    public function edit(OvertimeRequest $overtimeRequest): View
    {
        return view('admin.overtime-requests.edit', [
            'overtimeRequest' => $overtimeRequest,
            'employees' => $this->activeEmployees(),
        ]);
    }

    public function update(OvertimeRequestUpdateRequest $request, OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['status']) && $data['status'] !== $overtimeRequest->status) {
            $data['approved_by'] = Auth::id();
            $data['approved_at'] = now();
            if ($data['status'] !== OvertimeRequest::STATUS_REJECTED) {
                $data['reject_reason'] = null;
            }
        }

        $this->service->update($overtimeRequest, $data);

        return redirect()
            ->route('admin.overtime-requests.show', $overtimeRequest)
            ->with('success', 'Cập nhật yêu cầu tăng ca thành công.');
    }

    public function destroy(OvertimeRequest $overtimeRequest): RedirectResponse
    {
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

    public function updateStatus(UpdateOvertimeStatusRequest $request, OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $this->authorize('update', $overtimeRequest);

        $status = $request->validated('status');
        $rejectReason = $request->validated('reject_reason');

        if ($status === OvertimeRequest::STATUS_APPROVED && $overtimeRequest->isPending()) {
            try {
                $this->approvalService->approve($overtimeRequest, (int) Auth::id());
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors())->with('error', 'Không thể phê duyệt đơn tăng ca.');
            }

            return back()->with('success', 'Đã duyệt đơn tăng ca.');
        }

        if ($status === OvertimeRequest::STATUS_REJECTED && $overtimeRequest->isPending()) {
            try {
                $this->approvalService->reject($overtimeRequest, (int) Auth::id(), (string) $rejectReason);
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors())->with('error', 'Không thể từ chối đơn tăng ca.');
            }

            return back()->with('success', 'Đã từ chối đơn tăng ca.');
        }

        $overtimeRequest->update([
            'status' => $status,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'reject_reason' => $status === OvertimeRequest::STATUS_REJECTED ? $rejectReason : null,
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái đơn tăng ca.');
    }

    private function activeEmployees()
    {
        return Employee::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();
    }
}
