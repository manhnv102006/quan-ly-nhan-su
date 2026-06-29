<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignEmployeeKPIRequest;
use App\Http\Requests\Manager\UpdateEmployeeKPIScoreRequest;
use App\Models\Employee;
use App\Models\EmployeeKPI;
use App\Models\KPIAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class KPIController extends Controller
{
    /**
     * Danh sách KPI được giao cho Manager
     */
    public function index(): View
    {
        EmployeeKPI::markOverdueAsNotCompleted();

        $assignments = KPIAssignment::with([
                'kpi',
                'assignedBy',
            ])
            ->withCount('employeeKpis')
            ->where('manager_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('manager.kpis.index', compact('assignments'));
    }

    /**
     * Chi tiết KPI
     */
    public function show(KPIAssignment $assignment): View
    {
        EmployeeKPI::markOverdueAsNotCompleted();

        // Đảm bảo Manager chỉ xem KPI của chính mình
        abort_if($assignment->manager_id !== Auth::id(), 403);

        $assignment->load([
            'kpi',
            'assignedBy',
            'employeeKpis.employee', // Tải các mục tiêu đã giao cho nhân viên
        ]);

        return view('manager.kpis.show', compact('assignment'));
    }

    /**
     * Hiển thị form giao KPI cho nhân viên.
     */
    public function assign(KPIAssignment $assignment): View
    {
        // Đảm bảo Manager chỉ giao KPI của chính mình
        abort_if($assignment->manager_id !== Auth::id(), 403);

        $assignment->load('kpi');
        $employeesInDepartment = $this->getManagedEmployees();

        return view('manager.kpis.assign', compact('assignment', 'employeesInDepartment'));
    }

    /**
     * Lưu thông tin giao KPI cho nhân viên.
     */
    public function storeAssign(AssignEmployeeKPIRequest $request, KPIAssignment $assignment): RedirectResponse
    {
        // Đảm bảo Manager chỉ giao KPI của chính mình
        abort_if($assignment->manager_id !== Auth::id(), 403);

        $validated = $request->validated();

        // Tạo một mục tiêu (goal) mới cho nhân viên
        $assignment->employeeKpis()->create([
            'kpi_id' => $assignment->kpi_id,
            'employee_id' => $validated['employee_id'],
            'target' => $validated['target'], // Tên mục tiêu
            'comment' => $validated['comment'], // Mô tả công việc
            'deadline' => $validated['deadline'],
            'progress' => 0,
            'status' => EmployeeKPI::STATUS_PENDING,
            'score' => null,
        ]);

        return redirect()
            ->route('manager.kpis.show', $assignment)
            ->with('success', 'Giao mục tiêu cho nhân viên thành công.');
    }

    public function editScore(EmployeeKPI $employeeKpi): View
    {
        // Đảm bảo Manager chỉ chấm KPI của chính mình
        $employeeKpi->load(['employee', 'kpiAssignment.kpi', 'kpiAssignment.manager']);

        abort_if($employeeKpi->kpiAssignment?->manager_id !== Auth::id(), 403);

        return view('manager.kpis.score', compact('employeeKpi'));
    }

    public function updateScore(
        UpdateEmployeeKPIScoreRequest $request,
        EmployeeKPI $employeeKpi
    ): RedirectResponse {
        $validated = $request->validated();

        $employeeKpi->loadMissing(['kpiAssignment']);
        abort_if($employeeKpi->kpiAssignment?->manager_id !== Auth::id(), 403);

        // Chỉ cập nhật score/comment (KHÔNG đụng progress/status/target/deadline)
        $employeeKpi->update([
            'score' => $validated['score'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return redirect()
            ->route('manager.kpis.index')
            ->with('success', 'Chấm KPI thành công');
    }

    private function getManagedEmployees()
    {
        $manager = Auth::user()->employee;
        return Employee::where('department_id', $manager->department_id)->where('status', 'active')->get();
    }
}

