<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OvertimeApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $manager = Employee::where('user_id', Auth::id())->firstOrFail();
        $departmentId = $manager->department_id;

        $employees = Employee::query()
            ->where('department_id', $departmentId)
            ->orderBy('full_name')
            ->get();

        $overtimeRequests = OvertimeRequest::query()
            ->with(['employee.department'])
            ->whereHas('employee', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('work_date'), function ($query) use ($request) {
                $query->whereDate('work_date', $request->work_date);
            })
            ->when($request->filled('employee_id'), function ($query) use ($request) {
                $query->where('employee_id', $request->employee_id);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('manager.overtime-requests.index', [
            'overtimeRequests' => $overtimeRequests,
            'employees' => $employees,
            'filters' => $request->only(['status', 'work_date', 'employee_id']),
        ]);
    }
}
