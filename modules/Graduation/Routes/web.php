<?php

use Illuminate\Support\Facades\Route;
use Modules\Graduation\Http\Controllers\GraduationController;
use Modules\Graduation\Http\Controllers\GraduationDocumentController;

Route::middleware(['web', 'auth', 'permission:manage graduations'])->group(function (): void {
    Route::get('/graduations', [GraduationController::class, 'index'])->name('graduations.index');
    Route::get('/graduations/{student}', [GraduationController::class, 'show'])->name('graduations.show');
    Route::post('/graduations/{student}/approve', [GraduationController::class, 'approve'])->name('graduations.approve');
    Route::get('/graduation-records/{record}/certificate', [GraduationDocumentController::class, 'certificate'])->name('graduations.certificate');
    Route::get('/graduation-records/{record}/study-report', [GraduationDocumentController::class, 'studyReport'])->name('graduations.study-report');
});
