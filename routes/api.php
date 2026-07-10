<?php

use App\Http\Controllers\Api\FaceController;
use Illuminate\Support\Facades\Route;

Route::middleware('face.token')->prefix('face')->name('api.face.')->group(function () {
    Route::get('/employees', [FaceController::class, 'employees'])->name('employees');
    Route::get('/descriptors', [FaceController::class, 'descriptors'])->name('descriptors');
    Route::post('/descriptors', [FaceController::class, 'storeDescriptor'])->name('descriptors.store');
    Route::post('/attendance', [FaceController::class, 'attendance'])->name('attendance');
});
