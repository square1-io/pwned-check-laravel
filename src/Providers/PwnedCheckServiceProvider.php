<?php

namespace Square1\Laravel\PwnedCheck\Providers;

use Validator;
use Illuminate\Support\ServiceProvider;
use Square1\Laravel\PwnedCheck\Validator\Pwned;

class PwnedCheckServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('pwned-check.php'),
        ], 'config');

        Validator::extend('pwned', Pwned::class);
    }


    /**
     * Register and application services
     *
     * @return void
     */
    public function register()
    {
        if (!file_exists(config_path('pwned-check.php'))) {
            $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'pwned-check');
        }
    }
}
