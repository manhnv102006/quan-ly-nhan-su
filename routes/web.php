<?php

use App\Http\Controllers\Admin\AdminModuleController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\PayrollPeriodController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Employee\EmployeeLeaveController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'redirect']);

Route::get('/dashboard', [DashboardController::class, 'redirect'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    Route::get('/accounts', [AdminModuleController::class, 'accounts'])->name('accounts');
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
    Route::get('/attendances', [AdminModuleController::class, 'attendances'])->name('attendances');
    Route::get('/kpis', [AdminModuleController::class, 'kpis'])->name('kpis');
    Route::get('/payrolls', [PayrollController::class, 'index'])->name('payrolls');
    Route::get('/leave-requests', [\App\Http\Controllers\Admin\LeaveRequestController::class, 'index'])->name('leave-requests');
    Route::post('/leave-requests/{leaveRequest}/approve', [\App\Http\Controllers\Admin\LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('/leave-requests/{leaveRequest}/reject', [\App\Http\Controllers\Admin\LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
    Route::post('/payrolls/generate', [PayrollController::class, 'generate'])->name('payrolls.generate');
    Route::post('/payrolls/{payroll}/submit', [PayrollController::class, 'submit'])->name('payrolls.submit');
    Route::post('/payrolls/{payroll}/approve', [PayrollController::class, 'approve'])->name('payrolls.approve');
    Route::post('/payrolls/{payroll}/pay', [PayrollController::class, 'pay'])->name('payrolls.pay');
    Route::get('/payrolls/{payroll}/pdf', [PayrollController::class, 'exportPdf'])->name('payrolls.pdf');
    Route::get('/contracts', [AdminModuleController::class, 'contracts'])->name('contracts');
    Route::get('/recruitment', [AdminModuleController::class, 'recruitment'])->name('recruitment');
});

Route::middleware(['auth', 'verified', 'role:manager'])->group(function () {
    Route::get('/manager/dashboard', [DashboardController::class, 'manager'])->name('manager.dashboard');
    Route::get('/manager/leave-requests', [\App\Http\Controllers\Admin\LeaveRequestController::class, 'index'])->name('manager.leave-requests');
    Route::post('/manager/leave-requests/{leaveRequest}/approve', [\App\Http\Controllers\Admin\LeaveRequestController::class, 'approve'])->name('manager.leave-requests.approve');
    Route::post('/manager/leave-requests/{leaveRequest}/reject', [\App\Http\Controllers\Admin\LeaveRequestController::class, 'reject'])->name('manager.leave-requests.reject');
});

Route::middleware(['auth', 'verified', 'role:employee'])->group(function () {
    Route::get('/employee/dashboard', [DashboardController::class, 'employee'])->name('employee.dashboard');
});

Route::middleware(['auth', 'verified', 'role:employee,manager,admin'])->group(function () {
    Route::get('/employee/leave-requests', [EmployeeLeaveController::class, 'index'])->name('employee.leave-requests');
    Route::get('/employee/leave-requests/create', [EmployeeLeaveController::class, 'create'])->name('employee.leave-requests.create');
    Route::post('/employee/leave-requests', [EmployeeLeaveController::class, 'store'])->name('employee.leave-requests.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
