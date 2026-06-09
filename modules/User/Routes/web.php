<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\PortalDashboardController;
use Modules\Platform\Http\Controllers\ActivityLogController;
use Modules\User\Http\Controllers\AccessControlController;
use Modules\User\Http\Controllers\ProfileController;

/*
| User module routes. Loaded by UserServiceProvider inside the "web"
| middleware group, so session/cookies/CSRF already apply here.
*/

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('settings/profile', [ProfileController::class, 'updateUpload'])->name('profile.update.upload');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['web', 'auth', 'role:employee,super_admin'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'employee'])->name('dashboard');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs');
});

Route::middleware(['web', 'auth', 'permission:manage access control'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/access-control', [AccessControlController::class, 'index'])->name('access-control.index');
    Route::post('/access-control/users', [AccessControlController::class, 'storeUser'])->name('access-control.users.store');
    Route::put('/access-control/users/{user}', [AccessControlController::class, 'updateUser'])->name('access-control.users.update');
    Route::post('/access-control/roles', [AccessControlController::class, 'storeRole'])->name('access-control.roles.store');
    Route::put('/access-control/roles/{role}', [AccessControlController::class, 'updateRole'])->name('access-control.roles.update');
});
