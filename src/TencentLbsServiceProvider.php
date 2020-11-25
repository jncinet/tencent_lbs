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
        $this->app->singleton(TencentLbs::class, function () {
            return new TencentLbs();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/qihu_lbs.php' => config_path('qihu_lbs.php'),
        ], 'tencent-lbs');
    }
}
