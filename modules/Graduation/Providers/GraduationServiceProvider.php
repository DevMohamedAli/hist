<?php

namespace Modules\Graduation\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class GraduationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../../../resources/views/graduation', 'graduation');

        if (file_exists(__DIR__.'/../Routes/web.php')) {
            Route::middleware('web')->group(function (): void {
                $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
            });
        }
    }
}
