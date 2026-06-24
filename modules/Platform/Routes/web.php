<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Support\RoleDashboard;
use Modules\Platform\Http\Controllers\ActivityLogController;

/*
| Platform (app shell) routes. Loaded by PlatformServiceProvider inside the
| "web" middleware group. These are the host application's own surfaces —
| the welcome page, the dashboard frame, the appearance toggle — not owned by
| any domain (bounded-context) module.
*/

Route::inertia('/portal', 'Welcome')->name('portal.home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', fn (Request $request) => redirect()->to(RoleDashboard::pathFor($request->user())))->name('dashboard');

    // Generic UI preference — neither a User nor an Auth domain concern.
    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');

    // Activity Log UI for employees and super_admin (gate protection)

});

Route::middleware(['auth', 'role:employee,super_admin'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs');
    Route::post('/activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');
    Route::get('/activity-logs/analytics', [ActivityLogController::class, 'analytics'])->name('activity-logs.analytics');
    Route::get('/activity-logs/filter-options', [ActivityLogController::class, 'filterOptions'])->name('activity-logs.filter-options');
    Route::get('/activity-logs/views', [ActivityLogController::class, 'listViews'])->name('activity-logs.views.index');
    Route::post('/activity-logs/views', [ActivityLogController::class, 'saveView'])->name('activity-logs.views.save');
    Route::delete('/activity-logs/views', [ActivityLogController::class, 'deleteView'])->name('activity-logs.views.delete');
});
