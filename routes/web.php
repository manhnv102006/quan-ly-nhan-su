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

use App\Http\Controllers\Manager\EmployeeController as ManagerEmployeeController;

use App\Http\Controllers\Manager\KPIController as ManagerKPIController;

use App\Http\Controllers\Manager\NotificationController as ManagerNotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Employee\EmployeeLeaveController;
use App\Http\Controllers\Employee\EmployeePayrollController;
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
    Route::get('/attendances/{attendance}', [AttendanceController::class, 'show'])->name('attendances.show');
    Route::get('/attendances/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendances.edit');
    Route::put('/attendances/{attendance}', [AttendanceController::class, 'update'])->name('attendances.update');
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::get('/shifts/create', [ShiftController::class, 'create'])->name('shifts.create');
    Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
    Route::get('/shifts/{shift}/edit', [ShiftController::class, 'edit'])->name('shifts.edit');
    Route::put('/shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
    Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');
    Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::get('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave-requests.show');
    Route::patch('/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::patch('/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
    Route::get('/overtime-requests', [OvertimeRequestController::class, 'index'])->name('overtime-requests.index');
    Route::get('/overtime-requests/{overtimeRequest}', [OvertimeRequestController::class, 'show'])->name('overtime-requests.show');
    Route::patch('/overtime-requests/{overtimeRequest}/approve', [OvertimeRequestController::class, 'approve'])->name('overtime-requests.approve');
    Route::patch('/overtime-requests/{overtimeRequest}/reject', [OvertimeRequestController::class, 'reject'])->name('overtime-requests.reject');
    Route::get('/attendance-reports', [AttendanceReportController::class, 'index'])->name('attendance-reports.index');

    Route::get('/payrolls', [PayrollController::class, 'index'])->name('payrolls');

    Route::post('/payroll-periods/{payrollPeriod}/calculate', [PayrollPeriodController::class, 'calculate'])->name('payroll-periods.calculate');
    Route::post('/payroll-periods/{payrollPeriod}/approve', [PayrollPeriodController::class, 'approve'])->name('payroll-periods.approve');
    Route::post('/payroll-periods/{payrollPeriod}/pay', [PayrollPeriodController::class, 'pay'])->name('payroll-periods.pay');
    Route::post('/payroll-periods/{payrollPeriod}/close', [PayrollPeriodController::class, 'close'])->name('payroll-periods.close');
    Route::get('/payrolls/{payroll}/pdf', [PayrollController::class, 'exportPdf'])->name('payrolls.pdf');
    Route::get('/contracts', [AdminModuleController::class, 'contracts'])->name('contracts');

    Route::post('/payrolls/generate', [PayrollController::class, 'generate'])->name('payrolls.generate');
    Route::post('/payrolls/{payroll}/submit', [PayrollController::class, 'submit'])->name('payrolls.submit');
    Route::post('/payrolls/{payroll}/approve', [PayrollController::class, 'approve'])->name('payrolls.approve');
    Route::post('/payrolls/{payroll}/pay', [PayrollController::class, 'pay'])->name('payrolls.pay');

    Route::resource('contract-types', ContractTypeController::class)->except(['show']);
    Route::get('/contract-types/trash', [ContractTypeController::class, 'trash'])->name('contract-types.trash');
    Route::post('/contract-types/{id}/restore', [ContractTypeController::class, 'restore'])->name('contract-types.restore');

    Route::get('/contracts/trash', [ContractController::class, 'trash'])->name('contracts.trashed');
    Route::post('/contracts/{id}/restore', [ContractController::class, 'restore'])->name('contracts.restore');
    Route::delete('/contracts/{id}/force-delete', [ContractController::class, 'forceDelete'])->name('contracts.forceDelete');
    Route::post('/contracts/{contract}/extend', [ContractController::class, 'extend'])->name('contracts.extend');
    Route::post('/contracts/{contract}/terminate', [ContractController::class, 'terminate'])->name('contracts.terminate');
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

Route::middleware(['auth', 'verified', 'role:manager'])->group(function () {
    Route::get('/manager/dashboard', [DashboardController::class, 'manager'])->name('manager.dashboard');
    Route::get('/manager/employees', [ManagerEmployeeController::class, 'index'])->name('manager.employees.index');
    Route::get('/manager/employees/{employee}', [ManagerEmployeeController::class, 'show'])->name('manager.employees.show');
    Route::get('/manager/leave-requests', [LeaveRequestController::class, 'index'])->name('manager.leave-requests');
    Route::patch('/manager/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('manager.leave-requests.approve');
    Route::patch('/manager/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('manager.leave-requests.reject');
    Route::get('/manager/notifications', [ManagerNotificationController::class, 'index'])->name('manager.notifications.index');
    Route::get('/manager/notifications/create', [ManagerNotificationController::class, 'create'])->name('manager.notifications.create');
    Route::post('/manager/notifications', [ManagerNotificationController::class, 'store'])->name('manager.notifications.store');
    Route::patch('/manager/notifications/read-all', [ManagerNotificationController::class, 'markAllAsRead'])->name('manager.notifications.read-all');
    Route::patch('/manager/notifications/{notification}/read', [ManagerNotificationController::class, 'markAsRead'])->name('manager.notifications.read');
    Route::get('/manager/kpis', [ManagerKPIController::class, 'index'])->name('manager.kpis.index');
    Route::get('/manager/kpis/{assignment}', [ManagerKPIController::class, 'show'])->name('manager.kpis.show');
    Route::get('/manager/kpis/{assignment}/assign', [ManagerKPIController::class, 'assign'])->name('manager.kpis.assign');
    Route::post('/manager/kpis/{assignment}/assign', [ManagerKPIController::class, 'storeAssign'])->name('manager.kpis.store_assign');
});

Route::middleware(['auth', 'verified', 'role:employee'])->group(function () {
    Route::get('/employee/dashboard', [DashboardController::class, 'employee'])->name('employee.dashboard');

});

Route::middleware(['auth', 'verified', 'role:employee,manager,admin'])->group(function () {
    Route::get('/employee/leave-requests', [EmployeeLeaveController::class, 'index'])->name('employee.leave-requests');
    Route::get('/employee/leave-requests/create', [EmployeeLeaveController::class, 'create'])->name('employee.leave-requests.create');
    Route::post('/employee/leave-requests', [EmployeeLeaveController::class, 'store'])->name('employee.leave-requests.store');
    Route::get('/employee/payrolls', [EmployeePayrollController::class, 'index'])->name('employee.payrolls.index');
    Route::get('/employee/payrolls/{payroll}/pdf', [EmployeePayrollController::class, 'exportPdf'])->name('employee.payrolls.pdf');
    Route::get('/employee/payrolls/{payroll}', [EmployeePayrollController::class, 'show'])->name('employee.payrolls.show');
    Route::get('/employee/attendance', [EmployeeAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in/{shift}', [EmployeeAttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out/{shift}', [EmployeeAttendanceController::class, 'checkOut'])->name('attendance.check-out');

});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [UserNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [UserNotificationController::class, 'markAsRead'])->name('notifications.read');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
