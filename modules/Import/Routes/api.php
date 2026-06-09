<?php

use Illuminate\Support\Facades\Route;
use Modules\Import\Http\Controllers\ImportController;

/*
|--------------------------------------------------------------------------
| API Routes - Import Module
|--------------------------------------------------------------------------
|
| API endpoints for data import functionality including upload, preview,
| validation, and execution of import jobs.
|
*/

Route::middleware(['web', 'auth', 'role:employee,super_admin'])->prefix('import')->group(function (): void {
    // Upload file and create import job
    Route::post('/upload', [ImportController::class, 'upload'])->name('upload');

    Route::post('/courses/pdf', [ImportController::class, 'importPdfCourses'])->name('courses.pdf');

    // Get preview of data to be imported
    Route::get('/preview/{jobId}', [ImportController::class, 'preview'])->name('preview');

    // Validate data against schema
    Route::post('/validate/{jobId}', [ImportController::class, 'validate'])->name('validate');

    // Execute the import
    Route::post('/execute/{jobId}', [ImportController::class, 'execute'])->name('execute');

    // Get progress of import job
    Route::get('/progress/{jobId}', [ImportController::class, 'progress'])->name('progress');

    // Cancel import job
    Route::post('/cancel/{jobId}', [ImportController::class, 'cancel'])->name('cancel');
});
