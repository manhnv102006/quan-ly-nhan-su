<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\LeaderScopeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(private readonly LeaderScopeService $scope) {}

    public function index(Request $request): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $employees = $this->scope->teamMembersQuery($leader)
            ->with(['department', 'position', 'user'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('employee_code', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive', 'resigned'], true), fn ($q) => $q->where('status', $status))
            ->orderBy('full_name')
            ->paginate(12)
            ->withQueryString();

        $base = $this->scope->teamMembersQuery($leader);

        $stats = [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'inactive' => (clone $base)->where('status', 'inactive')->count(),
            'resigned' => (clone $base)->where('status', 'resigned')->count(),
        ];

        return view('leader.employees.index', compact('leader', 'employees', 'stats', 'search', 'status'));
    }

    public function show(Request $request, Employee $employee): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());

        if (! $this->scope->managesEmployee($leader, $employee)) {
            abort(403, 'Bạn chỉ được xem nhân viên thuộc nhóm mình.');
        }

        $employee->load(['department', 'position', 'user']);

        $kpis = $employee->employeeKpis()
            ->with('kpi')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        $attendances = $employee->attendances()
            ->with('shift')
            ->latest('attendance_date')
            ->limit(8)
            ->get();

        $leaveRequests = $employee->leaveRequests()
            ->latest()
            ->limit(5)
            ->get();

        $kpiStats = [
            'total' => $employee->employeeKpis()->count(),
            'completed' => $employee->employeeKpis()->where('status', 'completed')->count(),
            'avg_progress' => round((float) $employee->employeeKpis()->avg('progress'), 1),
        ];

        return view('leader.employees.show', compact(
            'leader',
            'employee',
            'kpis',
            'attendances',
            'leaveRequests',
            'kpiStats',
        ));
    }
}
