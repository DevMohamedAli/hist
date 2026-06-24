<?php

namespace Modules\Academic\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Academic\Console\Commands\AcademicAuditCommand;

class AcademicServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                AcademicAuditCommand::class,
            ]);
        }

        if (file_exists(__DIR__.'/../Routes/web.php')) {
            Route::middleware('web')->group(function () {
                $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
            });
        }
    }
}
