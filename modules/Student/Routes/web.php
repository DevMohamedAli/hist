<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\PortalDashboardController;
use Modules\Student\Http\Controllers\EnrollmentController;
use Modules\Student\Http\Controllers\GradeController;
use Modules\Student\Http\Controllers\StudentRegistrationController;
use Modules\Student\Http\Controllers\StudentSuspensionController;
use Modules\Student\Http\Controllers\StudentTransferController;

Route::middleware(['web', 'auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'student'])->name('dashboard');
    Route::get('/profile', [PortalDashboardController::class, 'studentProfile'])->name('profile');
});

Route::middleware(['web', 'auth', 'role:employee,super_admin'])->group(function () {
    Route::get('/students', [StudentRegistrationController::class, 'index'])->name('students.index');
    Route::get('/students/create', [StudentRegistrationController::class, 'create'])->name('students.create');
    Route::post('/students', [StudentRegistrationController::class, 'store'])->name('students.store');
    Route::get('/students/{student}', [StudentRegistrationController::class, 'show'])->name('students.show');
    Route::get('/students/{student}/edit', [StudentRegistrationController::class, 'edit'])->name('students.edit');
    Route::patch('/students/{student}', [StudentRegistrationController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [StudentRegistrationController::class, 'destroy'])->name('students.destroy');
    Route::patch('/students/{student}/reactivate', [StudentRegistrationController::class, 'reactivate'])->name('students.reactivate');

    Route::get('/students/{student}/enroll', [EnrollmentController::class, 'create'])->name('students.enroll.create');
    Route::post('/students/{student}/enroll', [EnrollmentController::class, 'store'])->name('students.enroll.store');

    Route::post('/students/{student}/suspend', [StudentSuspensionController::class, 'store'])->name('students.suspend');
    Route::post('/students/{student}/transfer', [StudentTransferController::class, 'store'])->name('students.transfer');
});

Route::middleware(['web', 'auth', 'role:teacher,employee,super_admin'])->group(function () {
    Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
    Route::get('/grades/classes/{class}', [GradeController::class, 'showClassGrades'])->name('grades.class');
    Route::post('/grades/record', [GradeController::class, 'store'])->name('grades.record');
});
