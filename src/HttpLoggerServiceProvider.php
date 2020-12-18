<?php namespace Zipzoft\HttpLogger;

use Illuminate\Support\ServiceProvider;

class HttpLoggerServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config.php', 'http-logger');

        $this->app->singleton(Writer::class, function($app) {
            return (new Manager($app))->driver();
        });
    }


    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config.php' => config_path('http-logger.php'),
            ], 'config');
        }
    }
}