<?php

namespace Modules\Website\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WebsiteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        RateLimiter::for('contact-form', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));

        Route::middleware('web')->group(function (): void {
            $this->loadRoutesFrom(__DIR__.'/../Routes/public.php');
            $this->loadRoutesFrom(__DIR__.'/../Routes/admin.php');
        });
    }
}
