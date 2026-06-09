<?php

namespace Modules\Qualification\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class QualificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        if (file_exists(__DIR__.'/../Routes/web.php')) {
            Route::middleware('web')->group(function () {
                $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
            });
        }
    }
}
