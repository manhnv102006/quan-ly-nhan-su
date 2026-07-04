<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AdminModuleController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EmployeeShiftController;
use App\Http\Controllers\Admin\InterviewController;
use App\Http\Controllers\Admin\JobPostController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\OvertimeRequestController;
use App\Http\Controllers\Admin\PayrollController;

use App\Http\Controllers\Admin\KPIAssignmentController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\ContractTypeController;


use App\Http\Controllers\Admin\KPIController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\NotificationController as UserNotificationController;

use App\Http\Controllers\Admin\PayrollPeriodController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\RecruitmentController;
use App\Http\Controllers\Admin\ShiftController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Employee\NotificationController as EmployeeNotificationController;
use App\Http\Controllers\Manager\EmployeeController as ManagerEmployeeController;
use App\Http\Controllers\Manager\KPIController as ManagerKPIController;
use App\Http\Controllers\Manager\NotificationController as ManagerNotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Employee\EmployeeLeaveController;
use App\Http\Controllers\Employee\EmployeePayrollController;

use App\Http\Controllers\Employee\EmployeeKPIController;


use App\Http\Controllers\Manager\LeaveApprovalController;
use App\Http\Controllers\Manager\OvertimeApprovalController;

use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\OvertimeController as EmployeeOvertimeController;



use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'redirect']);

Route::get('/dashboard', [DashboardController::class, 'redirect'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    Route::get('/notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
    Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts');
    Route::get('/accounts/trash', [AccountController::class, 'trash'])->name('accounts.trash');
    Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/{user}', [AccountController::class, 'show'])->name('accounts.show');
    Route::get('/accounts/{user}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
    Route::put('/accounts/{user}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{user}', [AccountController::class, 'destroy'])->name('accounts.destroy');
    Route::post('/accounts/{id}/restore', [AccountController::class, 'restore'])->name('accounts.restore');
    Route::delete('/accounts/{id}/force-delete', [AccountController::class, 'forceDelete'])->name('accounts.forceDelete');
    Route::patch('/accounts/{user}/toggle-status', [AccountController::class, 'toggleStatus'])->name('accounts.toggle-status');
    Route::patch('/accounts/{user}/reset-password', [AccountController::class, 'resetPassword'])->name('accounts.reset-password');
    Route::patch('/accounts/{user}/link-employee', [AccountController::class, 'linkEmployee'])->name('accounts.link-employee');
    Route::patch('/accounts/{user}/unlink-employee', [AccountController::class, 'unlinkEmployee'])->name('accounts.unlink-employee');
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments');
    Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::get('/departments/trash', [DepartmentController::class, 'trash'])->name('departments.trash');
    Route::get('/departments/{id}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
    Route::put('/departments/{id}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::get('/departments/{id}', [DepartmentController::class, 'show'])->name('departments.detail');
    Route::delete('/departments/{id}', [DepartmentController::class, 'destroy'])->name('departments.delete');
    Route::post('/departments/{id}/restore', [DepartmentController::class, 'restore'])->name('departments.restore');
    Route::delete('/departments/{id}/force-delete', [DepartmentController::class, 'forceDelete'])->name('departments.forceDelete');
    //Chức vụ
    Route::get('/positions', [PositionController::class, 'index'])->name('positions');
    Route::get('/positions/create', [PositionController::class, 'create'])->name('positions.create');
    Route::post('/positions', [PositionController::class, 'store'])->name('positions.store');
    Route::get('/positions/trash', [PositionController::class, 'trash'])->name('positions.trash');
    Route::get('/positions/{position}', [PositionController::class, 'show'])->name('positions.show');
    Route::get('/positions/{position}/edit', [PositionController::class, 'edit'])->name('positions.edit');
    Route::put('/positions/{position}', [PositionController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');
    Route::post('/positions/{id}/restore', [PositionController::class, 'restore'])->name('positions.restore');
    Route::delete('/positions/{id}/force-delete', [PositionController::class, 'forceDelete'])->name('positions.forceDelete');

    // Kỳ lương
    Route::get('/payroll-periods/trash', [PayrollPeriodController::class, 'trash'])->name('payroll-periods.trash');
    Route::post('/payroll-periods/{id}/restore', [PayrollPeriodController::class, 'restore'])->name('payroll-periods.restore');
    Route::delete('/payroll-periods/{id}/force-delete', [PayrollPeriodController::class, 'forceDelete'])->name('payroll-periods.forceDelete');
    Route::resource('payroll-periods', PayrollPeriodController::class);

    Route::get('/employees', [AdminModuleController::class, 'employees'])->name('employees');
    Route::get('/employees/trash', [EmployeeController::class, 'trash'])->name('employees.trash');
    Route::post('/employees/{id}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
    Route::delete('/employees/{id}/force-delete', [EmployeeController::class, 'forceDelete'])->name('employees.forceDelete');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{employee}/documents/download-all', [EmployeeController::class, 'downloadAllDocuments'])->name('employees.documents.download-all');
    Route::get('/employees/{employee}/documents/{document}/download', [EmployeeController::class, 'downloadDocument'])->name('employees.documents.download');
    Route::patch('/employees/{employee}/transfer-department', [EmployeeController::class, 'transferDepartment'])->name('employees.transfer-department');
    Route::patch('/employees/{employee}/link-account', [EmployeeController::class, 'linkAccount'])->name('employees.link-account');
    Route::patch('/employees/{employee}/unlink-account', [EmployeeController::class, 'unlinkAccount'])->name('employees.unlink-account');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    //gan ca lam viec cho nhan vien
    Route::get('/employee-shifts', [EmployeeShiftController::class, 'index'])->name('employee-shifts.index');
    Route::get('/employee-shifts/create', [EmployeeShiftController::class, 'create'])->name('employee-shifts.create');
    Route::post('/employee-shifts', [EmployeeShiftController::class, 'store'])->name('employee-shifts.store');

    Route::resource('kpis', KPIController::class);
    Route::resource('kpi-assignments', KPIAssignmentController::class)->parameters(['kpi-assignments' => 'assignment']);
    Route::patch('/kpi-assignments/{assignment}/approve', [KPIAssignmentController::class, 'approve'])->name('kpi-assignments.approve');
    Route::patch('/kpi-assignments/{assignment}/reject', [KPIAssignmentController::class, 'reject'])->name('kpi-assignments.reject');
    Route::patch('/kpi-assignments/{assignment}/complete', [KPIAssignmentController::class, 'complete'])->name('kpi-assignments.complete');

    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances');
    Route::get('/attendances/departments/{department}', [AttendanceController::class, 'department'])->name('attendances.department');
    Route::get('/attendances/{attendance}', [AttendanceController::class, 'show'])->name('attendances.show');
    Route::get('/attendances/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendances.edit');
    Route::put('/attendances/{attendance}', [AttendanceController::class, 'update'])->name('attendances.update');
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::get('/shifts/create', [ShiftController::class, 'create'])->name('shifts.create');
    Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
    Route::get('/shifts/{shift}/edit', [ShiftController::class, 'edit'])->name('shifts.edit');
    Route::put('/shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
    Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');
    Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests');
    Route::get('/leave-requests/departments/{department}', [LeaveRequestController::class, 'department'])->name('leave-requests.department');
    Route::get('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave-requests.show');
    Route::get('/overtime-requests/departments/{department}', [OvertimeRequestController::class, 'department'])->name('overtime-requests.department');
    Route::resource('overtime-requests', OvertimeRequestController::class)
        ->parameters(['overtime-requests' => 'overtime_request']);
    Route::patch('/overtime-requests/{overtime_request}/approve', [OvertimeRequestController::class, 'approve'])->name('overtime-requests.approve');
    Route::patch('/overtime-requests/{overtime_request}/reject', [OvertimeRequestController::class, 'reject'])->name('overtime-requests.reject');
    Route::patch('/overtime-requests/{overtime_request}/status', [OvertimeRequestController::class, 'updateStatus'])->name('overtime-requests.status');
    Route::get('/attendance-reports', [AttendanceReportController::class, 'index'])->name('attendance-reports.index');
    Route::get('/attendance-reports/departments/{department}', [AttendanceReportController::class, 'department'])->name('attendance-reports.department');

    Route::get('/payrolls', [PayrollController::class, 'index'])->name('payrolls');



    Route::get('/payroll-periods/{payrollPeriod}/departments/{department}', [PayrollPeriodController::class, 'department'])->name('payroll-periods.department');
    Route::post('/payroll-periods/{payrollPeriod}/calculate', [PayrollPeriodController::class, 'calculate'])->name('payroll-periods.calculate');
    Route::post('/payroll-periods/{payrollPeriod}/recalculate', [PayrollPeriodController::class, 'recalculate'])->name('payroll-periods.recalculate');
    Route::post('/payroll-periods/{payrollPeriod}/approve', [PayrollPeriodController::class, 'approve'])->name('payroll-periods.approve');
    Route::post('/payroll-periods/{payrollPeriod}/pay', [PayrollPeriodController::class, 'pay'])->name('payroll-periods.pay');
    Route::post('/payroll-periods/{payrollPeriod}/close', [PayrollPeriodController::class, 'close'])->name('payroll-periods.close');

    Route::resource('contract-types', ContractTypeController::class)->except(['show']);
    Route::get('/contract-types/trash', [ContractTypeController::class, 'trash'])->name('contract-types.trash');
    Route::post('/contract-types/{id}/restore', [ContractTypeController::class, 'restore'])->name('contract-types.restore');

    Route::get('/contracts/trash', [ContractController::class, 'trash'])->name('contracts.trashed');
    Route::post('/contracts/{contract}/restore', [ContractController::class, 'restore'])->name('contracts.restore');
    Route::delete('/contracts/{contract}/force-delete', [ContractController::class, 'forceDelete'])->name('contracts.forceDelete');
    Route::get('/contracts/{contract}/extend', [ContractController::class, 'extendForm'])->name('contracts.extend.form');
    Route::post('/contracts/{contract}/extend', [ContractController::class, 'extendStore'])->name('contracts.extend');
    Route::post('/contracts/{contract}/cancel', [ContractController::class, 'cancel'])->name('contracts.cancel');
    Route::resource('contracts', ContractController::class);

    Route::get('/recruitment', [RecruitmentController::class, 'index'])->name('recruitment');
    Route::get('/recruitment/job-posts', [JobPostController::class, 'index'])->name('recruitment.job-posts');
    Route::get('/recruitment/job-posts/create', [JobPostController::class, 'create'])->name('recruitment.job-posts.create');
    Route::post('/recruitment/job-posts', [JobPostController::class, 'store'])->name('recruitment.job-posts.store');
    Route::get('/recruitment/job-posts/{jobPost}/edit', [JobPostController::class, 'edit'])->name('recruitment.job-posts.edit');
    Route::put('/recruitment/job-posts/{jobPost}', [JobPostController::class, 'update'])->name('recruitment.job-posts.update');
    Route::delete('/recruitment/job-posts/{jobPost}', [JobPostController::class, 'destroy'])->name('recruitment.job-posts.destroy');
    Route::get('/recruitment/candidates', [CandidateController::class, 'index'])->name('recruitment.candidates');
    Route::get('/recruitment/candidates/create', [CandidateController::class, 'create'])->name('recruitment.candidates.create');
    Route::get('/recruitment/candidates/{candidate}', [CandidateController::class, 'show'])->name('recruitment.candidates.show');
    Route::get('/recruitment/candidates/{candidate}/edit', [CandidateController::class, 'edit'])->name('recruitment.candidates.edit');
    Route::post('/recruitment/candidates', [CandidateController::class, 'store'])->name('recruitment.candidates.store');
    Route::put('/recruitment/candidates/{candidate}', [CandidateController::class, 'update'])->name('recruitment.candidates.update');
    Route::post('/recruitment/candidates/{candidate}/convert-to-employee', [CandidateController::class, 'convertToEmployee'])->name('recruitment.candidates.convert-to-employee');
    Route::delete('/recruitment/candidates/{candidate}', [CandidateController::class, 'destroy'])->name('recruitment.candidates.destroy');
    Route::get('/recruitment/interviews', [InterviewController::class, 'index'])->name('recruitment.interviews');
    Route::get('/recruitment/interviews/create', [InterviewController::class, 'create'])->name('recruitment.interviews.create');
    Route::post('/recruitment/interviews', [InterviewController::class, 'store'])->name('recruitment.interviews.store');
    Route::put('/recruitment/interviews/{interview}', [InterviewController::class, 'update'])->name('recruitment.interviews.update');

    Route::get('/payrolls/{payroll}/pdf', [PayrollController::class, 'exportPdf'])->name('payrolls.pdf');
});

Route::middleware(['auth', 'verified', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'manager'])->name('dashboard');

    Route::get('/employees', [ManagerEmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/{employee}', [ManagerEmployeeController::class, 'show'])->name('employees.show');

    Route::get('/notifications', [ManagerNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/create', [ManagerNotificationController::class, 'create'])->name('notifications.create');
    Route::post('/notifications', [ManagerNotificationController::class, 'store'])->name('notifications.store');
    Route::get('/notifications/{notification}', [ManagerNotificationController::class, 'show'])->name('notifications.show');
    Route::patch('/notifications/read-all', [ManagerNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [ManagerNotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::get('/kpis', [ManagerKPIController::class, 'index'])->name('kpis.index');
    Route::get('/kpis/employee-kpis/{employeeKpi}/score', [ManagerKPIController::class, 'editScore'])->name('kpis.employee_kpis.score.edit');
    Route::put('/kpis/employee-kpis/{employeeKpi}/score', [ManagerKPIController::class, 'updateScore'])->name('kpis.employee_kpis.score.update');
    Route::get('/kpis/{assignment}', [ManagerKPIController::class, 'show'])->name('kpis.show');
    Route::get('/kpis/{assignment}/assign', [ManagerKPIController::class, 'assign'])->name('kpis.assign');
    Route::post('/kpis/{assignment}/assign', [ManagerKPIController::class, 'storeAssign'])->name('kpis.store_assign');

    Route::get('/overtime-requests', [OvertimeApprovalController::class, 'index'])->name('overtime-requests.index');
    Route::get('/overtime-requests/{overtimeRequest}', [OvertimeApprovalController::class, 'show'])->name('overtime-requests.show');
    Route::patch('/overtime-requests/{overtimeRequest}/approve', [OvertimeApprovalController::class, 'approve'])->name('overtime-requests.approve');
    Route::patch('/overtime-requests/{overtimeRequest}/reject', [OvertimeApprovalController::class, 'reject'])->name('overtime-requests.reject');
});

Route::middleware(['auth', 'verified', 'role:manager', 'leave.approval.manager'])
    ->prefix('manager/leave-requests')
    ->name('manager.leave-requests.')
    ->group(function () {
        Route::get('/', [LeaveApprovalController::class, 'index'])->name('index');
        Route::patch('/{leaveRequest}/approve', [LeaveApprovalController::class, 'approve'])->name('approve');
        Route::patch('/{leaveRequest}/reject', [LeaveApprovalController::class, 'reject'])->name('reject');
        Route::get('/{leaveRequest}', [LeaveApprovalController::class, 'show'])->name('show');
    });

Route::middleware(['auth', 'verified', 'role:employee'])->group(function () {
    Route::get('/employee/dashboard', [DashboardController::class, 'employee'])->name('employee.dashboard');

    Route::prefix('employee/kpis')->name('employee.kpis.')->group(function () {
        Route::get('/', [EmployeeKPIController::class, 'index'])->name('index');
        Route::get('/{employeeKpi}/edit', [EmployeeKPIController::class, 'edit'])->name('edit');
        Route::put('/{employeeKpi}', [EmployeeKPIController::class, 'update'])->name('update');
    });

    Route::get('/employee/notifications', [EmployeeNotificationController::class, 'index'])->name('employee.notifications.index');
    Route::get('/employee/notifications/{notification}', [EmployeeNotificationController::class, 'show'])->name('employee.notifications.show');
    Route::patch('/employee/notifications/read-all', [EmployeeNotificationController::class, 'markAllAsRead'])->name('employee.notifications.read-all');
    Route::patch('/employee/notifications/{notification}/read', [EmployeeNotificationController::class, 'markAsRead'])->name('employee.notifications.read');
});

Route::middleware(['auth', 'verified', 'role:employee,manager,admin'])->group(function () {
    Route::get('/employee/leave-requests', [EmployeeLeaveController::class, 'index'])->name('employee.leave-requests');
    Route::get('/employee/leave-requests/create', [EmployeeLeaveController::class, 'create'])->name('employee.leave-requests.create');
    Route::get('/employee/leave-requests/{leaveRequest}', [EmployeeLeaveController::class, 'show'])->name('employee.leave-requests.show');
    Route::post('/employee/leave-requests', [EmployeeLeaveController::class, 'store'])->name('employee.leave-requests.store');
    Route::get('/employee/payrolls', [EmployeePayrollController::class, 'index'])->name('employee.payrolls.index');
    Route::get('/employee/payrolls/{payroll}/pdf', [EmployeePayrollController::class, 'exportPdf'])->name('employee.payrolls.pdf');
    Route::get('/employee/payrolls/{payroll}', [EmployeePayrollController::class, 'show'])->name('employee.payrolls.show');
    Route::get('/employee/attendance', [EmployeeAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in/{shift}', [EmployeeAttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out/{shift}', [EmployeeAttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::post('/attendance/overtime/{overtime_request}/check-in', [EmployeeAttendanceController::class, 'overtimeCheckIn'])->name('attendance.overtime.check-in');
    Route::post('/attendance/overtime/{overtime_request}/check-out', [EmployeeAttendanceController::class, 'overtimeCheckOut'])->name('attendance.overtime.check-out');
    Route::get('/employee/overtime-requests', [EmployeeOvertimeController::class, 'index'])->name('employee.overtime-requests');
    Route::get('/employee/overtime-requests/create', [EmployeeOvertimeController::class, 'create'])->name('employee.overtime-requests.create');
    Route::post('/employee/overtime-requests', [EmployeeOvertimeController::class, 'store'])->name('employee.overtime-requests.store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [UserNotificationController::class, 'show'])->name('notifications.show');
    Route::patch('/notifications/read-all', [UserNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [UserNotificationController::class, 'markAsRead'])->name('notifications.read');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
