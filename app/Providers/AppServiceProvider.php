<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

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
        // Compartir el Meta Pixel ID con todas las vistas de Inertia
        Inertia::share([
            'metaPixelId' => config('services.meta.pixel_id'),
        ]);
    }
}
