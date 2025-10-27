<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Services
        $this->app->singleton(\App\Services\UserService::class, function ($app) {
            return new \App\Services\UserService();
        });

        $this->app->singleton(\App\Services\ClientService::class, function ($app) {
            return new \App\Services\ClientService();
        });

        $this->app->singleton(\App\Services\CompteService::class, function ($app) {
            return new \App\Services\CompteService();
        });

        $this->app->singleton(\App\Services\TransactionService::class, function ($app) {
            return new \App\Services\TransactionService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if(config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
