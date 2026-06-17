<?php

use App\Http\Controllers\Admin\AdminModuleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'redirect'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    Route::get('/accounts', [AdminModuleController::class, 'accounts'])->name('accounts');
    Route::get('/departments', [AdminModuleController::class, 'departments'])->name('departments');
    Route::get('/positions', [AdminModuleController::class, 'positions'])->name('positions');
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
