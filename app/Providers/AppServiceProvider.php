<?php

namespace App\Providers;
use Illuminate\Routing\Router;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */

    public function boot(Router $router)
    {
        // Register middleware aliases
        $router->aliasMiddleware('super.admin', \App\Http\Middleware\CheckSuperAdmin::class);
        $router->aliasMiddleware('admin', \App\Http\Middleware\CheckAdmin::class);
        $router->aliasMiddleware('staff', \App\Http\Middleware\CheckStaff::class);
        $router->aliasMiddleware('active.user', \App\Http\Middleware\CheckActiveUser::class);
    }
}
