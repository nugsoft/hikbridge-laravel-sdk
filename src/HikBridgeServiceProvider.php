<?php

namespace Nugsoft\HikBridge;

use Illuminate\Support\ServiceProvider;

class HikBridgeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/hikbridge.php', 'hikbridge');

        $this->app->singleton(HikBridgeClient::class, fn ($app) =>
            new HikBridgeClient($app['config']['hikbridge'])
        );

        $this->app->singleton(HikBridgeManager::class, fn ($app) =>
            new HikBridgeManager($app->make(HikBridgeClient::class))
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/hikbridge.php' => config_path('hikbridge.php'),
        ], 'hikbridge-config');
    }
}
