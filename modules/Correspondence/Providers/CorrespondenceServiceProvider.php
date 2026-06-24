<?php

namespace Modules\Correspondence\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Correspondence\Models\Correspondence;
use Modules\Correspondence\Policies\CorrespondencePolicy;

class CorrespondenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config.php', 'correspondence');
    }

    public function boot(): void
    {
        Gate::policy(Correspondence::class, CorrespondencePolicy::class);

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        Route::middleware('web')->group(function (): void {
            $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        });
    }
}
