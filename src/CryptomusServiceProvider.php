<?php

namespace Kristof\Cryptomus;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CryptomusServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/cryptomus.php' => config_path('cryptomus.php'),
        ], 'config');
        $timestamp = date('Y_m_d_His', time());

        $this->publishes([
            __DIR__.'/../database/migrations/create_cryptomus_webhook_calls_table.php.stub' => database_path("migrations/{$timestamp}_create_cryptomus_webhook_calls_table.php"),
        ], 'migrations');

        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cryptomus.php', 'cryptomus'
        );

        $this->app->bind('cryptomus', function ($app) {
            return new Cryptomus($app);
        });

        $this->app->alias('cryptomus', Cryptomus::class);
    }
}
