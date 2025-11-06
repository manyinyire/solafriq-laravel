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
        // Register services as singletons for better performance
        $this->app->singleton(\App\Services\OrderProcessingService::class);
        $this->app->singleton(\App\Services\InvoiceGeneratorService::class);
        $this->app->singleton(\App\Services\EmailNotificationService::class);
        $this->app->singleton(\App\Services\WarrantyService::class);
        $this->app->singleton(\App\Services\CartService::class);
        $this->app->singleton(\App\Services\SolarSystemBuilderService::class);
        $this->app->singleton(\App\Services\ImageOptimizationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}