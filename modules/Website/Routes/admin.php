<?php

use Illuminate\Support\Facades\Route;
use Modules\Website\Http\Controllers\Admin\BannerController;
use Modules\Website\Http\Controllers\Admin\ContactSubmissionController;
use Modules\Website\Http\Controllers\Admin\DashboardController;
use Modules\Website\Http\Controllers\Admin\FaqController;
use Modules\Website\Http\Controllers\Admin\PageController;
use Modules\Website\Http\Controllers\Admin\PostController;
use Modules\Website\Http\Controllers\Admin\SettingController;

Route::middleware(['auth'])
    ->prefix('admin/website')
    ->name('admin.website.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)
            ->middleware('permission:website.pages.view')
            ->name('dashboard');
        Route::get('/pages', [PageController::class, 'index'])
            ->middleware('permission:website.pages.view')
            ->name('pages.index');
        Route::post('/pages', [PageController::class, 'store'])
            ->middleware('permission:website.pages.create')
            ->name('pages.store');
        Route::put('/pages/{page}', [PageController::class, 'update'])
            ->middleware('permission:website.pages.update')
            ->name('pages.update');
        Route::get('/posts', [PostController::class, 'index'])
            ->middleware('permission:website.posts.view')
            ->name('posts.index');
        Route::post('/posts', [PostController::class, 'store'])
            ->middleware('permission:website.posts.create')
            ->name('posts.store');
        Route::put('/posts/{post}', [PostController::class, 'update'])
            ->middleware('permission:website.posts.update')
            ->name('posts.update');
        Route::get('/faqs', [FaqController::class, 'index'])
            ->middleware('permission:website.faqs.view')
            ->name('faqs.index');
        Route::post('/faqs', [FaqController::class, 'store'])
            ->middleware('permission:website.faqs.create')
            ->name('faqs.store');
        Route::put('/faqs/{faq}', [FaqController::class, 'update'])
            ->middleware('permission:website.faqs.update')
            ->name('faqs.update');
        Route::get('/banners', [BannerController::class, 'index'])
            ->middleware('permission:website.banners.view')
            ->name('banners.index');
        Route::post('/banners', [BannerController::class, 'store'])
            ->middleware('permission:website.banners.create')
            ->name('banners.store');
        Route::put('/banners/{banner}', [BannerController::class, 'update'])
            ->middleware('permission:website.banners.update')
            ->name('banners.update');
        Route::get('/settings', [SettingController::class, 'index'])
            ->middleware('permission:website.settings.manage')
            ->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])
            ->middleware('permission:website.settings.manage')
            ->name('settings.update');
        Route::get('/contact-submissions', [ContactSubmissionController::class, 'index'])
            ->middleware('permission:website.contact-submissions.view')
            ->name('contact-submissions.index');
    });
