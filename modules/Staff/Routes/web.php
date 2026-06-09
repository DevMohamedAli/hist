<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\PortalDashboardController;
use Modules\Staff\Http\Controllers\GradeController;
use Modules\Staff\Http\Controllers\InstructorController;

// Teacher routes (role: teacher)
Route::middleware(['web', 'auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'teacher'])->name('dashboard');
    Route::get('/classes/{courseClass}/grades', [GradeController::class, 'edit'])->name('classes.grades.edit');
    Route::put('/classes/{courseClass}/grades', [GradeController::class, 'update'])->name('classes.grades.update');
});

// Employee & Super Admin routes
Route::middleware(['web', 'auth', 'role:employee,super_admin'])->group(function () {
    // Instructors management (without nested qualification routes)
    Route::resource('staff/instructors', InstructorController::class)->parameters(['instructors' => 'instructor']);

});
