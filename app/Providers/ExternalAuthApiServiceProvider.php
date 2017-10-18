<?php

namespace App\Providers;

use App\Auth\ExternalAuthApiUserProvider;
use Illuminate\Support\ServiceProvider;


class ExternalAuthApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->app['auth']->provider('externalauthapi',function()
        {

            return new ExternalAuthApiUserProvider();
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
