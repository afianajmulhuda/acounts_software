<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Services\PermissionService;
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
    public function boot(): void
    {
        // Custom Blade directive for permission checks
        Blade::if('hasPermission', function (string $code) {
            return PermissionService::hasPermission($code);
        });

        Blade::if('hasAnyPermission', function (array $codes) {
            foreach ($codes as $code) {
                if (PermissionService::hasPermission($code)) {
                    return true;
                }
            }
            return false;
        });
    }
}
