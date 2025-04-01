<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\LoanService;
use App\Services\CryptoService;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoanService::class, function ($app) {
            return new LoanService();
        });

        $this->app->singleton(CryptoService::class, function ($app) {
            return new CryptoService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Additional boot logic if needed
    }
}
