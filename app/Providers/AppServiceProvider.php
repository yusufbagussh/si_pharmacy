<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
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

        Gate::define('farmasi', function (User $user) {
            return $user->kode_bagian === 'admin';
        });

        Blade::directive('convertSeconds', function ($seconds) {
            return "<?php
                \$hours = floor({$seconds} / 3600);
                \$minutes = floor(({$seconds} % 3600) / 60);
                \$seconds = {$seconds} % 60;
                echo sprintf('%02d:%02d:%02d', \$hours, \$minutes, \$seconds);
            ?>";
        });

        Blade::directive('checkCustomerType', function ($expression) {
            // Mengurai parameter
            $params = str_getcsv($expression, ',', "'");

            $customerGroupParam = trim($params[0]);
            $customerTypeParam = trim($params[1]);

            return "<?php
                switch ($customerGroupParam) {
                    case 'X004^500':
                        echo 'BPJS - Kemenkes';
                        break;
                    case 'X004^999':
                    case 'X004^251':
                    case 'X004^300':
                        echo 'Personal';
                        break;
                    case 'X004^100':
                    case 'X004^200':
                        echo 'Asuransi';
                        break;
                    default:
                        echo $customerTypeParam;
                        break;
                }
            ?>";
        });
    }
}
