<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
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
        Paginator::useBootstrap();

        Blade::directive('convertSeconds', function ($seconds) {
            return "<?php
                \$hours = floor({$seconds} / 3600);
                \$minutes = floor(({$seconds} % 3600) / 60);
                \$seconds = {$seconds} % 60;
                echo sprintf('%02d:%02d:%02d', \$hours, \$minutes, \$seconds);
            ?>";
        });
    }
}
