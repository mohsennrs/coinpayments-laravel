<?php

namespace Hattori\Coinpayments;

use Hattori\Coinpayments\Coinpayments;
use Illuminate\Support\ServiceProvider;

class CoinpaymentsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Views', 'coinpayments-laravel');

        $this->publishes([
            __DIR__.'/Config/coinpayments.php' => config_path('coinpayments.php'),
        ]);

        // $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/Config/coinpayments.php', 'coinpayments.php');

        // Register the service the package provides.
        $this->app->singleton('coinpayments', function ($app) {
            return new Coinpayments;
        });

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['coinpayments'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/Config/coinpayments.php' => config_path('coinpayments.php'),
        ], 'coinpayments.config');

    }
}
