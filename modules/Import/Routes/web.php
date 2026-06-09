<?php

use Illuminate\Support\Facades\Route;
use Modules\Import\Http\Controllers\ImportController;

Route::middleware(['auth', 'role:employee,super_admin'])->group(function (): void {
    Route::get('/imports', [ImportController::class, 'index'])->name('imports.index');
    Route::get('/imports/courses/pdf', [ImportController::class, 'pdfCourses'])->name('imports.courses.pdf');
});
