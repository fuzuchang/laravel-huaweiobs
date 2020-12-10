<?php

namespace Goodgay\HuaweiOBS;

use Illuminate\Support\ServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Application as LumenApplication;
use ObsV3\ObsClient;
use League\Flysystem\Filesystem;
class HWOBSServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->setUpConfig();
        $this->setStorage();
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


    protected function setStorage()
    {
        Storage::extend('hwobs', function ($app,$config) {
            $client = ObsClient::factory($config);
            return new Filesystem(new HwobsAdapter($client,'',$config['bucket']));
        });
    }
}
