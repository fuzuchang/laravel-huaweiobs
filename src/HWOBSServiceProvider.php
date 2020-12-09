<?php

namespace Goodgay\HuaweiOBS;

use Illuminate\Support\ServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use ObsV3\ObsClient;

class HWOBSServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->setUpConfig();
    }

    public function register()
    {
        $app = $this->app;

        $app->singleton('hwobs.factory', function($app) {
            return new Factory();
        });

        $app->singleton('hwobs', function($app) {
            return new Manager($app, $app['hwobs.factory']);
        });

        $app->alias('hwobs', Manager::class);

        $app->singleton(ObsClient::class, function(Container $app) {
            return $app->make('hwobs')->bucket();
        });

    }

    protected function setUpConfig(): void
    {
        $source = __DIR__ . '/config/hwobs.php';

        if ($this->app instanceof LaravelApplication) {
            $this->publishes([$source => config_path('hwobs.php')], 'config');
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('hwobs');
        }

        $this->mergeConfigFrom($source, 'hwobs');
    }
}
