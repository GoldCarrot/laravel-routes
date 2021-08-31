<?php

namespace GoldcarrotLaravel\Routes;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->namespace = config('routes.namespace', $this->namespace);

        $this->publishes([
            __DIR__ . '/../config/routes.php' => config_path('routes.php')
        ], 'config');

        $this->routes(function () {
            RouteModuleRegistrar::setRootNamespace($this->namespace)->register(config('routes.modules'));
        });
    }

    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__ . '/../config/routes.php', 'routes');
    }
}
