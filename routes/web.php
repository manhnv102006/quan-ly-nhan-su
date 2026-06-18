<?php

use App\Http\Controllers\Admin\AdminModuleController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'redirect']);

Route::get('/dashboard', [DashboardController::class, 'redirect'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    Route::get('/accounts', [AdminModuleController::class, 'accounts'])->name('accounts');
    Route::get('/departments', [AdminModuleController::class, 'departments'])->name('departments');

    
    //Chức vụ 
    Route::get('/positions', [PositionController::class, 'index'])->name('positions');
    Route::get('/positions/create', [PositionController::class, 'create'])->name('positions.create');
    Route::post('/positions', [PositionController::class, 'store'])->name('positions.store');
    Route::get('/positions/{position}/edit', [PositionController::class, 'edit'])->name('positions.edit');
    Route::put('/positions/{position}', [PositionController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');
    Route::get('/positions/trash', [PositionController::class, 'trash'])->name('positions.trash');
    Route::post('/positions/{id}/restore', [PositionController::class, 'restore'])->name('positions.restore');
    Route::delete('/positions/{id}/force-delete', [PositionController::class, 'forceDelete'])->name('positions.forceDelete');


    Route::get('/employees', [AdminModuleController::class, 'employees'])->name('employees');
    Route::get('/attendances', [AdminModuleController::class, 'attendances'])->name('attendances');
    Route::get('/kpis', [AdminModuleController::class, 'kpis'])->name('kpis');
    Route::get('/payrolls', [AdminModuleController::class, 'payrolls'])->name('payrolls');
    Route::get('/contracts', [AdminModuleController::class, 'contracts'])->name('contracts');
    Route::get('/recruitment', [AdminModuleController::class, 'recruitment'])->name('recruitment');
});

Route::middleware(['auth', 'verified', 'role:manager'])->group(function () {
    Route::get('/manager/dashboard', [DashboardController::class, 'manager'])->name('manager.dashboard');
});

Route::middleware(['auth', 'verified', 'role:employee'])->group(function () {
    Route::get('/employee/dashboard', [DashboardController::class, 'employee'])->name('employee.dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
