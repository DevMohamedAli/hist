<?php

use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\PortalLoginController;
use Modules\Auth\Http\Controllers\SecurityController;

/*
| Auth module routes. Loaded by AuthServiceProvider inside the "web"
| middleware group. Login/registration/2FA routes themselves are provided
| by Fortify; these are the app-owned security settings screens.
*/

Route::middleware('guest')->group(function () {
    Route::inertia('/student/login', 'auth/StudentLogin')->name('student.login');
    Route::post('/student/login', [PortalLoginController::class, 'studentLogin'])->middleware('throttle:portal-login')->name('student.login.submit');

    Route::inertia('/teacher/login', 'auth/TeacherLogin')->name('teacher.login');
    Route::post('/teacher/login', [PortalLoginController::class, 'teacherLogin'])->middleware('throttle:portal-login')->name('teacher.login.submit');

    Route::inertia('/employee/login', 'auth/EmployeeLogin')->name('employee.login');
    Route::post('/employee/login', [PortalLoginController::class, 'employeeLogin'])->middleware('throttle:portal-login')->name('employee.login.submit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('settings/security', [SecurityController::class, 'edit'])
        ->middleware(RequirePassword::class)
        ->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');
});
