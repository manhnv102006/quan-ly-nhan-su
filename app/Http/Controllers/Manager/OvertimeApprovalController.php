<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Concerns\ResolvesCurrentEmployee;
use App\Http\Controllers\Controller;
use App\Http\Requests\OvertimeRequestRejectRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\OvertimeRequest;
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
        $manager = $this->currentManager();
        $departmentId = $manager->department_id;
        $filters = $request->only(['search', 'status', 'work_date', 'employee_id', 'department_id']);

        $overtimeRequests = OvertimeRequest::query()
            ->with(['employee.department'])
            ->whereHas('employee', fn ($query) => $query->where('department_id', $departmentId))
            ->filter($filters)
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('manager.overtime-requests.index', [
            'overtimeRequests' => $overtimeRequests,
            'employees' => Employee::query()
                ->where('department_id', $departmentId)
                ->orderBy('full_name')
                ->get(),
            'managedDepartment' => Department::find($departmentId),
            'filters' => $filters,
        ]);
    }

    public function show(OvertimeRequest $overtimeRequest): View
    {
        $this->authorize('view', $overtimeRequest);

        $overtimeRequest->load(['employee.department', 'approver', 'histories.actor']);

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
