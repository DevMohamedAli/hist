<?php

use Illuminate\Support\Facades\Route;
use Modules\Qualification\Http\Controllers\QualificationController;

Route::middleware(['web', 'auth', 'role:employee,super_admin'])->group(function () {
    Route::get('qualifications', [QualificationController::class, 'index'])->name('qualifications.index');
    Route::post('qualifications', [QualificationController::class, 'store'])->name('qualifications.store');
    Route::patch('qualifications/{qualification}', [QualificationController::class, 'update'])->name('qualifications.update');
    Route::delete('qualifications/{qualification}', [QualificationController::class, 'destroy'])->name('qualifications.destroy');
});
