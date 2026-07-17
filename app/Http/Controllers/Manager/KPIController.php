<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignEmployeeKPIRequest;
use App\Http\Requests\Manager\AssignKpiToLeaderRequest;
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
                'kpi',
                'assignedBy',
                'leaderEmployee',
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
            'leaderEmployee',
            'teamReport',
            'employeeKpis.employee',
        ]);

        return view('manager.kpis.show', compact('assignment'));
    }

    /**
     * Hiển thị form giao KPI cho nhân viên.
     */
    public function assign(KPIAssignment $assignment): View
    {
        abort_if($assignment->manager_id !== Auth::id(), 403);

        if ($assignment->isDelegatedToLeader()) {
            abort(403, 'KPI này đã giao cho Trưởng nhóm phân bổ. Manager không giao trực tiếp cho nhân viên.');
        }

        $assignment->load('kpi');
        $employeesInDepartment = $this->getManagedEmployees();

        return view('manager.kpis.assign', compact('assignment', 'employeesInDepartment'));
    }

    public function assignLeader(KPIAssignment $assignment): View
    {
        abort_if($assignment->manager_id !== Auth::id(), 403);
        abort_if($assignment->isDelegatedToLeader(), 403, 'KPI này đã được giao cho Trưởng nhóm.');

        $assignment->load('kpi');
        $leadersInDepartment = $this->getLeadersInDepartment();

        return view('manager.kpis.assign-leader', compact('assignment', 'leadersInDepartment'));
    }

    public function storeAssignLeader(AssignKpiToLeaderRequest $request, KPIAssignment $assignment): RedirectResponse
    {
        abort_if($assignment->manager_id !== Auth::id(), 403);
        abort_if($assignment->isDelegatedToLeader(), 403);

        $validated = $request->validated();

        $assignment->update([
            'leader_employee_id' => $validated['leader_employee_id'],
            'leader_assigned_at' => now(),
            'note' => $validated['note'] ?? $assignment->note,
        ]);

        $leader = Employee::query()->find($validated['leader_employee_id']);
        if ($leader?->user_id) {
            app(\App\Services\NotificationService::class)->sendToUser(
                (int) $leader->user_id,
                'KPI nhóm mới: '.$assignment->kpi_title,
                'Manager đã giao KPI nhóm cho bạn. Vui lòng phân bổ cho thành viên trong nhóm.',
                Auth::id(),
            );
        }

        return redirect()
            ->route('manager.kpis.show', $assignment)
            ->with('success', 'Đã giao KPI nhóm cho Trưởng nhóm.');
    }

    /**
     * Lưu thông tin giao KPI cho nhân viên.
     */
    public function storeAssign(AssignEmployeeKPIRequest $request, KPIAssignment $assignment): RedirectResponse
    {
        abort_if($assignment->manager_id !== Auth::id(), 403);
        abort_if($assignment->isDelegatedToLeader(), 403, 'KPI này đã giao cho Trưởng nhóm.');

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

    private function getLeadersInDepartment()
    {
        $managerEmployee = Auth::user()->employee;

        if (! $managerEmployee) {
            return collect();
        }

        return Employee::query()
            ->where('department_id', $managerEmployee->department_id)
            ->where('status', 'active')
            ->whereHas('user.role', fn ($q) => $q->where('name', 'leader'))
            ->orderBy('full_name')
            ->get();
    }

}

