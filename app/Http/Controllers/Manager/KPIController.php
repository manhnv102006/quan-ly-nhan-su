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
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KPIController extends Controller
{
    /**
     * Danh sách KPI được giao cho Manager
     */
    public function index(): View
    {
        EmployeeKPI::markOverdueAsNotCompleted();

        $assignments = KPIAssignment::with([
                'kpi.tasks',
                'assignedBy',
                'employeeKpis.employee',
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
            'kpi.tasks',
            'assignedBy',
            'employeeKpis.employee',
        ]);

        return view('manager.kpis.show', compact('assignment'));
    }

    /**
     * Hiển thị form giao KPI cho nhân viên.
     */
    public function assign(KPIAssignment $assignment): View|RedirectResponse
    {
        abort_if($assignment->manager_id !== Auth::id(), 403);

        if ($assignment->employeeKpis()->exists()) {
            return redirect()
                ->route('manager.kpis.show', $assignment)
                ->with('error', 'KPI này đã được giao cho nhân viên. Mỗi KPI chỉ giao một lần.');
        }

        $assignment->load('kpi');
        $employeesInDepartment = $this->getManagedEmployees();

        return view('manager.kpis.assign', compact('assignment', 'employeesInDepartment'));
    }

    /**
     * Lưu thông tin giao KPI cho nhân viên.
     */
    public function storeAssign(AssignEmployeeKPIRequest $request, KPIAssignment $assignment): RedirectResponse
    {
        abort_if($assignment->manager_id !== Auth::id(), 403);

        if ($assignment->employeeKpis()->exists()) {
            return redirect()
                ->route('manager.kpis.show', $assignment)
                ->with('error', 'KPI này đã được giao cho nhân viên. Mỗi KPI chỉ giao một lần.');
        }

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

        // Chỉ cập nhật score/review (KHÔNG đụng progress/status/target/deadline/comment)
        $employeeKpi->update([
            'score' => $validated['score'],
            'review' => $validated['review'] ?? null,
        ]);

        return redirect()
            ->route('manager.kpis.index')
            ->with('success', 'Chấm KPI thành công');
    }

    private function getManagedEmployees()
    {
        $managerEmployee = Auth::user()->employee;

        if (! $managerEmployee) {
            return collect();
        }

        // Chỉ nhân viên ĐÃ có tài khoản đăng nhập (role khác manager),
        // cùng phòng ban với manager và đang hoạt động mới được giao KPI.
        return Employee::query()
            ->where('department_id', $managerEmployee->department_id)
            ->where('status', 'active')
            ->where('id', '!=', $managerEmployee->id)
            ->whereHas('user', function ($q) {
                $q->whereHas('role', function ($roleQ) {
                    $roleQ->where('name', '!=', 'manager');
                });
            })
            ->get();
    }

}

