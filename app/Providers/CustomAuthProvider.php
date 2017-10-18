<?php

namespace App\Providers;

use App\Auth\CustomUserProvider;
use Illuminate\Support\ServiceProvider;


class CustomAuthProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->app['auth']->extend('custom',function()
        {
            die(var_dump('22'));
            return new CustomUserProvider();
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
