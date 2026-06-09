<?php

namespace Modules\Import\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ImportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Load web routes
        if (file_exists(__DIR__ . '/../Routes/web.php')) {
            Route::middleware('web')->group(function (): void {
                $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
            });
        }

        // Load API routes (routes file handles the prefix)
        if (file_exists(__DIR__ . '/../Routes/api.php')) {
            Route::prefix('api')->group(function (): void {
                $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
            });
        }
    }
}
