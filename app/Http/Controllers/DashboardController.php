<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Candidate;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Interview;
use App\Models\JobPost;
use App\Models\KPIAssignment;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Models\EmployeeKPI;
use App\Services\AdminNotificationService;
use App\Services\AdminPendingApprovalService;
use App\Services\ManagerScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $pending = app(AdminPendingApprovalService::class)->counts();

        $employeeStatus = [
            'active' => Employee::where('status', 'active')->count(),
            'inactive' => Employee::where('status', 'inactive')->count(),
            'resigned' => Employee::where('status', 'resigned')->count(),
        ];

        $todayAttendance = Attendance::query()
            ->whereDate('attendance_date', today())
            ->whereIn('status', ['present', 'late'])
            ->count();

        $todayLate = Attendance::query()
            ->whereDate('attendance_date', today())
            ->where('status', 'late')
            ->count();

        $expiringContracts = Contract::query()
            ->with(['employee.department'])
            ->where('status', Contract::STATUS_ACTIVE)
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [today()->toDateString(), today()->addDays(30)->toDateString()])
            ->orderBy('end_date')
            ->limit(5)
            ->get();

        $departmentHeadcount = Department::query()
            ->where('status', 'active')
            ->withCount(['employees as active_employees_count' => fn ($query) => $query->where('status', 'active')])
            ->orderByDesc('active_employees_count')
            ->limit(6)
            ->get(['id', 'department_code', 'department_name']);

        $maxDepartmentCount = max(1, (int) $departmentHeadcount->max('active_employees_count'));

        $monthlyAttendance = DB::table('attendances')
            ->where('attendance_date', '>=', now()->subMonths(5)->startOfMonth()->toDateString())
            ->whereIn('status', ['present', 'late'])
            ->selectRaw("DATE_FORMAT(attendance_date, '%Y-%m') as month_key, COUNT(*) as total")
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get();

        $maxMonthlyAttendance = max(1, (int) $monthlyAttendance->max('total'));

        $approvalQueue = collect()
            ->merge(
                LeaveRequest::query()
                    ->with(['employee.department'])
                    ->where('status', LeaveRequest::STATUS_PENDING)
                    ->whereHas('employee', fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->whereHas('role', fn ($roleQuery) => $roleQuery->where('name', Role::MANAGER))))
                    ->latest()
                    ->limit(4)
                    ->get()
                    ->map(fn (LeaveRequest $item) => [
                        'type' => 'leave',
                        'title' => $item->employee?->full_name ?? '—',
                        'subtitle' => 'Đơn nghỉ phép · '.($item->employee?->department?->department_name ?? '—'),
                        'meta' => optional($item->start_date)->format('d/m/Y').' → '.optional($item->end_date)->format('d/m/Y'),
                        'status' => $item->status,
                        'url' => route('admin.leave-requests.show', $item),
                        'created_at' => $item->created_at,
                    ])
            )
            ->merge(
                OvertimeRequest::query()
                    ->with(['employee.department'])
                    ->where('status', OvertimeRequest::STATUS_PENDING)
                    ->whereHas('employee', fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->whereHas('role', fn ($roleQuery) => $roleQuery->where('name', Role::MANAGER))))
                    ->latest()
                    ->limit(4)
                    ->get()
                    ->map(fn (OvertimeRequest $item) => [
                        'type' => 'overtime',
                        'title' => $item->employee?->full_name ?? '—',
                        'subtitle' => 'Đơn tăng ca · '.($item->employee?->department?->department_name ?? '—'),
                        'meta' => optional($item->work_date)->format('d/m/Y'),
                        'status' => $item->status,
                        'url' => route('admin.overtime-requests.show', $item),
                        'created_at' => $item->created_at,
                    ])
            )
            ->merge(
                KPIAssignment::query()
                    ->with(['kpi', 'manager'])
                    ->where('status', 'pending')
                    ->latest()
                    ->limit(3)
                    ->get()
                    ->map(fn (KPIAssignment $item) => [
                        'type' => 'kpi',
                        'title' => $item->kpi?->title ?? 'KPI',
                        'subtitle' => 'Giao KPI · '.($item->manager?->name ?? '—'),
                        'meta' => 'Chờ phê duyệt giao KPI',
                        'status' => $item->status,
                        'url' => route('admin.kpi-assignments.index'),
                        'created_at' => $item->created_at,
                    ])
            )
            ->sortByDesc('created_at')
            ->take(6)
            ->values();

        $recentEmployees = Employee::query()
            ->with(['department', 'position'])
            ->orderByDesc('hire_date')
            ->limit(5)
            ->get();

        $recentJobs = JobPost::query()
            ->with('department')
            ->latest()
            ->take(4)
            ->get();

        $payrollSnapshot = [
            'open' => PayrollPeriod::where('status', 'open')->count(),
            'calculated' => PayrollPeriod::where('status', 'calculated')->count(),
            'approved' => PayrollPeriod::where('status', 'approved')->count(),
            'paid' => PayrollPeriod::where('status', 'paid')->count(),
        ];

        $recruitmentSnapshot = [
            'new_candidates' => Candidate::where('status', 'new')->count(),
            'open_jobs' => JobPost::where('status', 'open')->count(),
            'pending_interviews' => Interview::where('result', 'pending')->count(),
        ];

        $heroMetrics = [
            [
                'label' => 'Nhân viên đang làm',
                'value' => $employeeStatus['active'],
                'hint' => number_format($employeeStatus['inactive'] + $employeeStatus['resigned']).' ngoài biên chế hoạt động',
                'route' => 'admin.employees',
                'tone' => 'from-violet-500 to-indigo-600',
                'icon' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0z',
            ],
            [
                'label' => 'Chấm công hôm nay',
                'value' => $todayAttendance,
                'hint' => number_format($todayLate).' lượt đi muộn',
                'route' => 'admin.attendances',
                'tone' => 'from-cyan-500 to-blue-600',
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            [
                'label' => 'Việc cần xử lý',
                'value' => $pending['total'],
                'hint' => 'Phê duyệt, lương, tuyển dụng',
                'route' => app(AdminPendingApprovalService::class)->primaryActionUrl($pending),
                'tone' => 'from-amber-500 to-orange-600',
                'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'is_url' => true,
            ],
            [
                'label' => 'Hợp đồng sắp hết hạn',
                'value' => $expiringContracts->count(),
                'hint' => 'Trong 30 ngày tới',
                'route' => 'admin.contracts.index',
                'tone' => 'from-rose-500 to-pink-600',
                'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
            ],
        ];

        $moduleStats = [
            ['label' => 'Tài khoản', 'value' => User::count(), 'route' => 'admin.accounts', 'tone' => 'text-violet-600 bg-violet-50'],
            ['label' => 'Phòng ban', 'value' => Department::count(), 'route' => 'admin.departments', 'tone' => 'text-cyan-600 bg-cyan-50'],
            ['label' => 'Chức vụ', 'value' => Position::count(), 'route' => 'admin.positions', 'tone' => 'text-indigo-600 bg-indigo-50'],
            ['label' => 'Bảng lương', 'value' => Payroll::count(), 'route' => 'admin.payrolls', 'tone' => 'text-emerald-600 bg-emerald-50'],
            ['label' => 'Đơn nghỉ', 'value' => LeaveRequest::count(), 'route' => 'admin.leave-requests', 'tone' => 'text-amber-600 bg-amber-50'],
            ['label' => 'Ứng viên', 'value' => Candidate::count(), 'route' => 'admin.recruitment', 'tone' => 'text-rose-600 bg-rose-50'],
        ];

        return view('dashboard.admin', [
            'user' => $user,
            'firstName' => collect(explode(' ', trim($user->name)))->filter()->first() ?? $user->name,
            'pending' => $pending,
            'heroMetrics' => $heroMetrics,
            'moduleStats' => $moduleStats,
            'employeeStatus' => $employeeStatus,
            'todayAttendance' => $todayAttendance,
            'todayLate' => $todayLate,
            'expiringContracts' => $expiringContracts,
            'departmentHeadcount' => $departmentHeadcount,
            'maxDepartmentCount' => $maxDepartmentCount,
            'monthlyAttendance' => $monthlyAttendance,
            'maxMonthlyAttendance' => $maxMonthlyAttendance,
            'approvalQueue' => $approvalQueue,
            'recentEmployees' => $recentEmployees,
            'recentJobs' => $recentJobs,
            'payrollSnapshot' => $payrollSnapshot,
            'recruitmentSnapshot' => $recruitmentSnapshot,
            'unreadNotifications' => app(AdminNotificationService::class)->unreadCount($user),
        ]);
    }

    public function manager(): View
    {
        EmployeeKPI::markOverdueAsNotCompleted();

        /** @var User $user */
        $user = Auth::user();
        $scope = app(ManagerScopeService::class);
        $managerEmployee = $scope->resolveManagerEmployee($user);
        $employeeProfile = $managerEmployee
            ? $this->employeeProfileFromModel($managerEmployee)
            : $this->employeeProfile($user);
        $department = $this->managedDepartment($employeeProfile);
        $unreadNotifications = $this->unreadNotificationsCount($user);

        $teamCount = 0;
        $activeCount = 0;
        $pendingLeaves = 0;
        $totalLeaves = 0;
        $kpiInProgress = 0;
        $openJobs = 0;
        $todayCheckIns = 0;
        $teamMembers = collect();
        $approvalQueue = collect();
        $recruitmentPosts = collect();
        $kpiStatus = $this->aggregateDefaults([
            'pending' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'not_completed' => 0,
            'average_progress' => 0,
        ]);

        if ($managerEmployee) {
            $managedEmployeeIds = $scope->managedEmployeesQuery($managerEmployee)->pluck('id');
            $managedDepartmentIds = $scope->managedDepartmentIds($managerEmployee);

            $teamCount = $managedEmployeeIds->count();

            $activeCount = DB::table('employees')
                ->whereIn('id', $managedEmployeeIds)
                ->where('status', 'active')
                ->count();

            $pendingLeaves = DB::table('leave_requests')
                ->whereIn('employee_id', $managedEmployeeIds)
                ->where('status', 'pending')
                ->count();

            $totalLeaves = DB::table('leave_requests')
                ->whereIn('employee_id', $managedEmployeeIds)
                ->count();

            $kpiInProgress = DB::table('employee_kpis')
                ->whereIn('employee_id', $managedEmployeeIds)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();

            if ($managedDepartmentIds !== []) {
                $openJobs = DB::table('job_posts')
                    ->whereIn('department_id', $managedDepartmentIds)
                    ->where('status', 'open')
                    ->count();
            }

            $todayCheckIns = DB::table('attendances')
                ->whereIn('employee_id', $managedEmployeeIds)
                ->whereDate('attendance_date', today()->toDateString())
                ->whereIn('status', ['present', 'late'])
                ->count();

            $teamMembers = DB::table('employees')
                ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
                ->whereIn('employees.id', $managedEmployeeIds)
                ->orderByDesc('employees.hire_date')
                ->limit(6)
                ->get([
                    'employees.full_name',
                    'employees.employee_code',
                    'employees.status',
                    'employees.hire_date',
                    'employees.phone',
                    'positions.position_name',
                ]);

            $approvalQueue = DB::table('leave_requests')
                ->join('employees', 'employees.id', '=', 'leave_requests.employee_id')
                ->whereIn('employees.id', $managedEmployeeIds)
                ->orderByRaw("
                    CASE leave_requests.status
                        WHEN 'pending' THEN 0
                        WHEN 'approved' THEN 1
                        ELSE 2
                    END
                ")
                ->orderByDesc('leave_requests.created_at')
                ->limit(5)
                ->get([
                    'employees.full_name',
                    'leave_requests.leave_type',
                    'leave_requests.start_date',
                    'leave_requests.end_date',
                    'leave_requests.status',
                    'leave_requests.created_at',
                ]);

            if ($managedDepartmentIds !== []) {
                $recruitmentPosts = DB::table('job_posts')
                    ->whereIn('department_id', $managedDepartmentIds)
                    ->latest()
                    ->limit(3)
                    ->get([
                        'title',
                        'quantity',
                        'status',
                        'created_at',
                    ]);
            }

            $kpiStatus = DB::table('employee_kpis')
                ->whereIn('employee_id', $managedEmployeeIds)
                ->selectRaw("
                    SUM(CASE WHEN employee_kpis.status = 'pending' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN employee_kpis.status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
                    SUM(CASE WHEN employee_kpis.status = 'completed' THEN 1 ELSE 0 END) AS completed,
                    SUM(CASE WHEN employee_kpis.status = 'not_completed' THEN 1 ELSE 0 END) AS not_completed,
                    COALESCE(AVG(employee_kpis.progress), 0) AS average_progress
                ")
                ->first() ?? $kpiStatus;
        }

        return view('dashboard.manager', [
            'employeeProfile' => $employeeProfile,
            'department' => $department,
            'unreadNotifications' => $unreadNotifications,
            'teamCount' => $teamCount,
            'activeCount' => $activeCount,
            'pendingLeaves' => $pendingLeaves,
            'totalLeaves' => $totalLeaves,
            'kpiInProgress' => $kpiInProgress,
            'openJobs' => $openJobs,
            'todayCheckIns' => $todayCheckIns,
            'teamMembers' => $teamMembers,
            'approvalQueue' => $approvalQueue,
            'recruitmentPosts' => $recruitmentPosts,
            'kpiStatus' => $kpiStatus,
        ]);
    }

    public function employee(): View
    {
        EmployeeKPI::markOverdueAsNotCompleted();

        /** @var User $user */
        $user = Auth::user();
        $employeeProfile = $this->employeeProfile($user);
        $unreadNotifications = $this->unreadNotificationsCount($user);

        $attendanceSummary = $this->aggregateDefaults([
            'shifts_completed' => 0,
            'late_count' => 0,
            'work_hours' => 0,
        ]);
        $leaveSummary = $this->aggregateDefaults([
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
        ]);
        $kpiSummary = $this->aggregateDefaults([
            'total' => 0,
            'completed' => 0,
            'average_progress' => 0,
        ]);
        $latestPayroll = null;
        $attendanceHistory = collect();
        $kpiItems = collect();
        $notifications = collect();
        $contract = null;

        if ($employeeProfile) {
            $attendanceSummary = DB::table('attendances')
                ->where('employee_id', $employeeProfile->id)
                ->whereBetween('attendance_date', [
                    now()->startOfMonth()->toDateString(),
                    now()->endOfMonth()->toDateString(),
                ])
                ->selectRaw("
                    SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) AS shifts_completed,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) AS late_count,
                    COALESCE(SUM(work_hours), 0) AS work_hours
                ")
                ->first() ?? $attendanceSummary;

            $leaveSummary = DB::table('leave_requests')
                ->where('employee_id', $employeeProfile->id)
                ->whereBetween('start_date', [
                    now()->startOfYear()->toDateString(),
                    now()->endOfYear()->toDateString(),
                ])
                ->selectRaw("
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected
                ")
                ->first() ?? $leaveSummary;

            $kpiSummary = DB::table('employee_kpis')
                ->where('employee_id', $employeeProfile->id)
                ->selectRaw("
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
                    COALESCE(AVG(progress), 0) AS average_progress
                ")
                ->first() ?? $kpiSummary;

            $latestPayroll = DB::table('payrolls')
                ->join('payroll_periods', 'payroll_periods.id', '=', 'payrolls.payroll_period_id')
                ->where('payrolls.employee_id', $employeeProfile->id)
                ->orderByDesc('payroll_periods.year')
                ->orderByDesc('payroll_periods.month')
                ->first([
                    'payrolls.id',
                    'payrolls.basic_salary',
                    'payrolls.allowance',
                    'payrolls.bonus',
                    'payrolls.deduction',
                    'payrolls.total_salary',

                    'payroll_periods.status as status',

                    'payroll_periods.status',

                    'payroll_periods.month',
                    'payroll_periods.year',
                ]);

            $attendanceHistory = DB::table('attendances')
                ->leftJoin('shifts', 'shifts.id', '=', 'attendances.shift_id')
                ->where('attendances.employee_id', $employeeProfile->id)
                ->orderByDesc('attendances.attendance_date')
                ->limit(5)
                ->get([
                    'attendances.attendance_date',
                    'attendances.check_in',
                    'attendances.check_out',
                    'attendances.work_hours',
                    'attendances.status',
                    'shifts.shift_name',
                    'shifts.start_time',
                    'shifts.end_time',
                ]);

            $kpiItems = DB::table('employee_kpis')
                ->join('kpis', 'kpis.id', '=', 'employee_kpis.kpi_id')
                ->where('employee_kpis.employee_id', $employeeProfile->id)
                ->orderByDesc('employee_kpis.updated_at')
                ->limit(4)
                ->get([
                    'kpis.title',
                    'employee_kpis.progress',
                    'employee_kpis.score',
                    'employee_kpis.status',
                    'employee_kpis.updated_at',
                ]);

            $contract = DB::table('contracts')
                ->where('employee_id', $employeeProfile->id)
                ->orderByDesc('start_date')
                ->first([
                    'contract_code',
                    'salary',
                    'start_date',
                    'end_date',
                    'status',
                ]);
        }

        $notifications = DB::table('notification_users')
            ->join('notifications', 'notifications.id', '=', 'notification_users.notification_id')
            ->where('notification_users.user_id', $user->id)
            ->latest('notification_users.created_at')
            ->limit(4)
            ->get([
                'notifications.title',
                'notifications.type',
                'notifications.created_at',
                'notification_users.is_read',
            ]);

        if ($notifications->isEmpty()) {
            $notifications = DB::table('notifications')
                ->latest()
                ->limit(3)
                ->get([
                    'title',
                    'type',
                    'created_at',
                    DB::raw('1 AS is_read'),
                ]);
        }

        return view('dashboard.employee', [
            'employeeProfile' => $employeeProfile,
            'unreadNotifications' => $unreadNotifications,
            'attendanceSummary' => $attendanceSummary,
            'leaveSummary' => $leaveSummary,
            'kpiSummary' => $kpiSummary,
            'latestPayroll' => $latestPayroll,
            'attendanceHistory' => $attendanceHistory,
            'kpiItems' => $kpiItems,
            'notifications' => $notifications,
            'contract' => $contract,
        ]);
    }

    public function redirect(): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        return redirect()->route($user->dashboardRouteName());
    }

    private function employeeProfile(User $user): ?object
    {
        return DB::table('employees')
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->where('employees.user_id', $user->id)
            ->first([
                'employees.id',
                'employees.user_id',
                'employees.department_id',
                'employees.position_id',
                'employees.employee_code',
                'employees.full_name',
                'employees.email',
                'employees.phone',
                'employees.address',
                'employees.hire_date',
                'employees.status as employee_status',
                'departments.department_code',
                'departments.department_name',
                'positions.position_name',
            ]);
    }

    private function employeeProfileFromModel(Employee $employee): ?object
    {
        return DB::table('employees')
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->where('employees.id', $employee->id)
            ->first([
                'employees.id',
                'employees.user_id',
                'employees.department_id',
                'employees.position_id',
                'employees.employee_code',
                'employees.full_name',
                'employees.email',
                'employees.phone',
                'employees.address',
                'employees.hire_date',
                'employees.status as employee_status',
                'departments.department_code',
                'departments.department_name',
                'positions.position_name',
            ]);
    }

    private function managedDepartment(?object $employeeProfile): ?object
    {
        if (! $employeeProfile) {
            return null;
        }

        $department = DB::table('departments')
            ->where('manager_id', $employeeProfile->id)
            ->first([
                'id',
                'department_code',
                'department_name',
                'description',
                'status',
            ]);

        if ($department || ! $employeeProfile->department_id) {
            return $department;
        }

        return DB::table('departments')
            ->where('id', $employeeProfile->department_id)
            ->first([
                'id',
                'department_code',
                'department_name',
                'description',
                'status',
            ]);
    }

    private function unreadNotificationsCount(User $user): int
    {
        return app(AdminNotificationService::class)->unreadCount($user);
    }

    private function aggregateDefaults(array $values): object
    {
        return (object) $values;
    }
}
