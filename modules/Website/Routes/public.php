<?php

use Illuminate\Support\Facades\Route;
use Modules\Website\Http\Controllers\Public\ContactController;
use Modules\Website\Http\Controllers\Public\FaqController;
use Modules\Website\Http\Controllers\Public\HomeController;
use Modules\Website\Http\Controllers\Public\PageController;

Route::get('/', HomeController::class)->name('home');
Route::get('/faqs', FaqController::class)->name('website.faqs.index');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact-form')->name('website.contact.store');
Route::get('/pages/{slug}', [PageController::class, 'show'])->name('website.pages.show');
