<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Candidate;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPost;
use App\Models\Payroll;
use App\Models\Position;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('dashboard.admin', [
            'stats' => [
                ['label' => 'Tài khoản', 'value' => User::count(), 'color' => 'blue', 'route' => 'admin.accounts'],
                ['label' => 'Phòng ban', 'value' => Department::count(), 'color' => 'cyan', 'route' => 'admin.departments'],
                ['label' => 'Chức vụ', 'value' => Position::count(), 'color' => 'indigo', 'route' => 'admin.positions'],
                ['label' => 'Nhân viên', 'value' => Employee::count(), 'color' => 'sky', 'route' => 'admin.employees'],
                ['label' => 'Chấm công', 'value' => Attendance::count(), 'color' => 'teal', 'route' => 'admin.attendances'],
                ['label' => 'Bảng lương', 'value' => Payroll::count(), 'color' => 'emerald', 'route' => 'admin.payrolls'],

                ['label' => 'Hợp đồng', 'value' => Contract::count(), 'color' => 'violet', 'route' => 'admin.contracts.index'],
                ['label' => 'Sắp hết hạn', 'value' => Contract::where('status', 'active')
                    ->whereNotNull('end_date')
                    ->whereBetween('end_date', [today()->toDateString(), today()->addDays(30)->toDateString()])
                    ->count(), 'color' => 'rose', 'route' => 'admin.contracts.index'],
                ['label' => 'Đơn nghỉ phép', 'value' => LeaveRequest::count(), 'color' => 'amber', 'route' => 'admin.leave-requests.index'],
                ['label' => 'Ứng viên', 'value' => Candidate::count(), 'color' => 'rose', 'route' => 'admin.recruitment'],
            ],
            'recentJobs' => JobPost::query()->latest()->take(5)->get(),
        ]);
    }

    public function manager(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $employeeProfile = $this->employeeProfile($user);
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
            'average_progress' => 0,
        ]);

        if ($department) {
            $teamCount = DB::table('employees')
                ->where('department_id', $department->id)
                ->count();

            $activeCount = DB::table('employees')
                ->where('department_id', $department->id)
                ->where('status', 'active')
                ->count();

            $pendingLeaves = DB::table('leave_requests')
                ->join('employees', 'employees.id', '=', 'leave_requests.employee_id')
                ->where('employees.department_id', $department->id)
                ->where('leave_requests.status', 'pending')
                ->count();

            $totalLeaves = DB::table('leave_requests')
                ->join('employees', 'employees.id', '=', 'leave_requests.employee_id')
                ->where('employees.department_id', $department->id)
                ->count();

            $kpiInProgress = DB::table('employee_kpis')
                ->join('employees', 'employees.id', '=', 'employee_kpis.employee_id')
                ->where('employees.department_id', $department->id)
                ->whereIn('employee_kpis.status', ['pending', 'in_progress'])
                ->count();

            $openJobs = DB::table('job_posts')
                ->where('department_id', $department->id)
                ->where('status', 'open')
                ->count();

            $todayCheckIns = DB::table('attendances')
                ->join('employees', 'employees.id', '=', 'attendances.employee_id')
                ->where('employees.department_id', $department->id)
                ->whereDate('attendances.attendance_date', today()->toDateString())
                ->whereIn('attendances.status', ['present', 'late'])
                ->count();

            $teamMembers = DB::table('employees')
                ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
                ->where('employees.department_id', $department->id)
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
                ->where('employees.department_id', $department->id)
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

            $recruitmentPosts = DB::table('job_posts')
                ->where('department_id', $department->id)
                ->latest()
                ->limit(3)
                ->get([
                    'title',
                    'quantity',
                    'status',
                    'created_at',
                ]);

            $kpiStatus = DB::table('employee_kpis')
                ->join('employees', 'employees.id', '=', 'employee_kpis.employee_id')
                ->where('employees.department_id', $department->id)
                ->selectRaw("
                    SUM(CASE WHEN employee_kpis.status = 'pending' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN employee_kpis.status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
                    SUM(CASE WHEN employee_kpis.status = 'completed' THEN 1 ELSE 0 END) AS completed,
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
                    'payrolls.basic_salary',
                    'payrolls.allowance',
                    'payrolls.bonus',
                    'payrolls.deduction',
                    'payrolls.total_salary',
                    'payrolls.status',
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
