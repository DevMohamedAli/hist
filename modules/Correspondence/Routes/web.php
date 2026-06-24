<?php

use Illuminate\Support\Facades\Route;
use Modules\Correspondence\Http\Controllers\CorrespondenceAttachmentController;
use Modules\Correspondence\Http\Controllers\CorrespondenceController;

Route::middleware(['auth', 'permission:correspondence.view-own'])
    ->prefix('correspondence')
    ->name('correspondence.')
    ->group(function (): void {
        Route::get('/inbox', [CorrespondenceController::class, 'inbox'])->name('inbox');
        Route::get('/sent', [CorrespondenceController::class, 'sent'])->name('sent');
        Route::get('/create', [CorrespondenceController::class, 'create'])->middleware('permission:correspondence.create')->name('create');
        Route::post('/', [CorrespondenceController::class, 'store'])->middleware('permission:correspondence.create')->name('store');
        Route::get('/attachments/{attachment}', [CorrespondenceAttachmentController::class, 'show'])->name('attachments.show');
        Route::get('/{correspondence}', [CorrespondenceController::class, 'show'])->name('show');
        Route::post('/{correspondence}/submit', [CorrespondenceController::class, 'submit'])->name('submit');
        Route::post('/{correspondence}/approve', [CorrespondenceController::class, 'approve'])->middleware('permission:correspondence.approve')->name('approve');
        Route::post('/{correspondence}/dispatch', [CorrespondenceController::class, 'dispatch'])->middleware('permission:correspondence.send')->name('dispatch');
        Route::post('/{correspondence}/reply', [CorrespondenceController::class, 'reply'])->middleware('permission:correspondence.reply')->name('reply');
        Route::post('/{correspondence}/attachments', [CorrespondenceAttachmentController::class, 'store'])->name('attachments.store');
        Route::post('/{correspondence}/complete', [CorrespondenceController::class, 'complete'])->middleware('permission:correspondence.complete')->name('complete');
        Route::post('/{correspondence}/archive', [CorrespondenceController::class, 'archive'])->middleware('permission:correspondence.archive')->name('archive');
    });
