<?php

namespace App\Providers;

use App\Services\Contract\ExchangeRate as ExchangeRateContract;
use App\Services\ExchangeRateHost;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        ExchangeRateContract::class => ExchangeRateHost::class,
    ];

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
        //
    }
}
