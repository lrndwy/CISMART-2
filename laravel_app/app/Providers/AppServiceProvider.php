<?php

namespace App\Providers;

use App\Models\Order;
use App\Policies\OrderPolicy;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
    public function boot(): void
    {
        if (PHP_VERSION_ID >= 80500) {
            error_reporting(E_ALL & ~E_DEPRECATED);
        }

        // Register policies
        Gate::policy(Order::class, OrderPolicy::class);

        if (App::environment('production')) {
            URL::forceScheme('https');
        }
    }
}
