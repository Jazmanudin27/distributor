<?php

namespace App\Providers;

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
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        if (!in_array(request()->getHost(), ['localhost', '127.0.0.1', '::1'])) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
