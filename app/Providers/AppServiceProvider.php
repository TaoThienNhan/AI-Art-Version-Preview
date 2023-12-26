<?php

namespace App\Providers;

use App\Core\Plugin\CustomCuratorPlugin;
use Awcodes\Curator\CuratorPlugin;
use BezhanSalleh\FilamentLanguageSwitch\Enums\Placement;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
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
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['en','vi'])
                ->visible(outsidePanels: true)
                ->outsidePanelPlacement(Placement::BottomRight)
                ->renderHook('panels::global-search.before')
                ->circular();
        });
    }
}
