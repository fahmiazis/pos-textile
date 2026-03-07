<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
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
    // public function boot(): void
    // {
    //     Vite::prefetch(concurrency: 3);
    // }
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        
        if (str_starts_with(config('app.url'), 'https')) {
            \URL::forceScheme('https');
        }

        if ($prefix = request()->server('HTTP_X_FORWARDED_PREFIX')) {
            \URL::forceRootUrl(config('app.url'));
            app('router')->prefix($prefix);
        }
    }
}
