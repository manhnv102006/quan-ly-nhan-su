<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeKPIRequest;
use App\Models\Employee;
use App\Models\EmployeeKPI;
use App\Models\KPI;
use Illuminate\Http\Request;

class EmployeeKPIController extends Controller
{
    /**
     * Danh sách KPI đã giao cho nhân viên.
     */
    public function index(Request $request)
    {
        $query = EmployeeKPI::with(['employee.department', 'kpi']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            })->orWhereHas('kpi', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employeeKpis = $query->latest()->paginate(10)->withQueryString();

        return view('admin.employee-kpis.index', compact('employeeKpis'));
    }

    /**
     * Form giao KPI cho nhân viên.
     */
    public function create()
    {
        $kpis = KPI::with('departments')
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        $employees = Employee::with(['department', 'user.role'])
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();

        return view('admin.employee-kpis.create', compact('kpis', 'employees'));
    }

    /**
     * Lưu việc giao KPI cho nhân viên.
     */
    public function store(StoreEmployeeKPIRequest $request)
    {
        $data = $request->validated();

        $kpi = KPI::findOrFail($data['kpi_id']);

        EmployeeKPI::create([
            'assignment_id' => null,
            'kpi_id' => $kpi->id,
            'employee_id' => $data['employee_id'],
            'target' => $kpi->target,
            'deadline' => $kpi->end_date,
            'comment' => $data['note'] ?? null,
            'progress' => 0,
            'status' => EmployeeKPI::STATUS_PENDING,
            'score' => null,
        ]);

        return redirect()
            ->route('admin.employee-kpis.index')
            ->with('success', 'Giao KPI cho nhân viên thành công');
    }
}
