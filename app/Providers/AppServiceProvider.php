<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Awcodes\Curator\Facades\Curator;

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
        Curator::acceptedFileTypes([
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/svg+xml',
            'video/mp4',
            'application/pdf',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
        ])->maxSize(10240); // 10MB limit
    }
}
