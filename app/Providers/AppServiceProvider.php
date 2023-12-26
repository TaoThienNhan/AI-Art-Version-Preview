<?php

namespace App\Providers;

use App\Core\Plugin\CustomCuratorPlugin;
use Awcodes\Curator\CuratorPlugin;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CuratorPlugin::class, function ($app) {
            return new CustomCuratorPlugin();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
