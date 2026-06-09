<?php

use Illuminate\Support\Facades\Route;
use Modules\Import\Http\Controllers\ImportController;

Route::middleware(['web', 'auth', 'role:employee,super_admin'])->group(function (): void {
    Route::post('/import/upload', [ImportController::class, 'upload']);
    Route::get('/import/preview/{job_id}', [ImportController::class, 'preview']);
    Route::post('/import/validate/{job_id}', [ImportController::class, 'validate']);
    Route::post('/import/execute/{job_id}', [ImportController::class, 'execute']);
    Route::get('/import/progress/{job_id}', [ImportController::class, 'progress']);
    Route::post('/import/cancel/{job_id}', [ImportController::class, 'cancel']);
});
