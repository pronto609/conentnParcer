<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Helper\LastGeneratedState;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LastGeneratedState::class, function ($app) {
            return new LastGeneratedState();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
