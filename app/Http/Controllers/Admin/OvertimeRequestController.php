<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OvertimeRequestStoreRequest;
use App\Http\Requests\OvertimeRequestUpdateRequest;
use App\Models\OvertimeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OvertimeRequestController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(OvertimeRequest::class, 'overtimeRequest');
    }

    public function index(): View
    {
        $overtimeRequests = OvertimeRequest::with(['employee', 'approver'])
            ->latest()
            ->paginate(15);

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
        return view('admin.overtime-requests.create');
    }

    public function store(OvertimeRequestStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['employee_id'] = $data['employee_id'] ?? $request->user()?->employee?->id;
        $data['status'] = $data['status'] ?? OvertimeRequest::STATUS_PENDING;

        if (! $data['employee_id']) {
            return back()
                ->withErrors(['employee_id' => 'Không xác định được nhân viên tạo đơn.'])
                ->withInput();
        }

        OvertimeRequest::create($data);

        return redirect()
            ->route('admin.overtime-requests.index')
            ->with('success', 'Tạo yêu cầu tăng ca thành công.');
    }

    public function show(OvertimeRequest $overtimeRequest): View
    {
        $overtimeRequest->load(['employee', 'approver']);

        return view('admin.overtime-requests.show', compact('overtimeRequest'));
    }

    public function edit(OvertimeRequest $overtimeRequest): View
    {
        if ($overtimeRequest->status !== OvertimeRequest::STATUS_PENDING) {
            abort(403, 'Chỉ được chỉnh sửa đơn ở trạng thái Pending.');
        }

        return view('admin.overtime-requests.edit', compact('overtimeRequest'));
    }

    public function update(OvertimeRequestUpdateRequest $request, OvertimeRequest $overtimeRequest): RedirectResponse
    {
        if ($overtimeRequest->status !== OvertimeRequest::STATUS_PENDING) {
            return redirect()
                ->route('admin.overtime-requests.show', $overtimeRequest)
                ->with('error', 'Đơn đã duyệt/từ chối, không thể chỉnh sửa.');
        }

        $overtimeRequest->update($request->validated());

        return redirect()
            ->route('admin.overtime-requests.show', $overtimeRequest)
            ->with('success', 'Cập nhật yêu cầu tăng ca thành công.');
    }

    public function destroy(OvertimeRequest $overtimeRequest): RedirectResponse
    {
        if ($overtimeRequest->status !== OvertimeRequest::STATUS_PENDING) {
            return redirect()
                ->route('admin.overtime-requests.show', $overtimeRequest)
                ->with('error', 'Đơn đã duyệt/từ chối, không thể xóa.');
        }

        $overtimeRequest->delete();

        return redirect()
            ->route('admin.overtime-requests.index')
            ->with('success', 'Xóa yêu cầu tăng ca thành công.');
    }
}