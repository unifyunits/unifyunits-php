<?php

namespace UnifyUnits\Laravel;

use Illuminate\Support\ServiceProvider;
use UnifyUnits\Laravel\Contracts\UnifyUnitsClient as ClientContract;

class UnifyUnitsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/unifyunits.php', 'unifyunits');

        $this->app->singleton(ClientContract::class, fn ($app) => new UnifyUnitsClient(
            $app['config']->get('unifyunits.base_url'),
            $app['config']->get('unifyunits.api_key'),
            (float) $app['config']->get('unifyunits.timeout', 10),
        ));
        $this->app->alias(ClientContract::class, 'unifyunits');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config/unifyunits.php' => config_path('unifyunits.php')], 'unifyunits-config');
        }
    }
}
