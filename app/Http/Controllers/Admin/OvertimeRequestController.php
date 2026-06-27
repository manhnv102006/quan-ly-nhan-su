<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OvertimeRequestStoreRequest;
use App\Http\Requests\OvertimeRequestUpdateRequest;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
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
        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();

        return view('admin.overtime-requests.create', compact('employees'));
    }

    public function store(OvertimeRequestStoreRequest $request): RedirectResponse
    {
        $data = $this->normalizePayload($request->validated());
        $data['employee_id'] = $data['employee_id'] ?? $request->user()?->employee?->id;
        $data['status'] = OvertimeRequest::STATUS_PENDING;

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

        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();

        return view('admin.overtime-requests.edit', compact('overtimeRequest', 'employees'));
    }

    public function update(OvertimeRequestUpdateRequest $request, OvertimeRequest $overtimeRequest): RedirectResponse
    {
        if ($overtimeRequest->status !== OvertimeRequest::STATUS_PENDING) {
            return redirect()
                ->route('admin.overtime-requests.show', $overtimeRequest)
                ->with('error', 'Đơn đã duyệt/từ chối, không thể chỉnh sửa.');
        }

        $overtimeRequest->update($this->normalizePayload($request->validated()));

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

    private function normalizePayload(array $payload): array
    {
        if (! isset($payload['total_hours']) || $payload['total_hours'] === null || $payload['total_hours'] === '') {
            $start = Carbon::createFromFormat('H:i', $payload['start_time']);
            $end = Carbon::createFromFormat('H:i', $payload['end_time']);
            $payload['total_hours'] = round($end->diffInMinutes($start) / 60, 2);
        }

        return $payload;
    }
}