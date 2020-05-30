<?php

namespace Qihucms\TencentLbs;

use Illuminate\Support\ServiceProvider;

class TencentLbsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Lbs::class, function () {
            return new Lbs();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
