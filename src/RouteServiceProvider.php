<?php

namespace GoldcarrotLaravel\Routes;

use Arr;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteServiceProvider extends ServiceProvider
{
    private const ROUTE_SEPARATOR = '/';
    private const NAMESPACE_SEPARATOR = "\\";

    public function __construct($app)
    {
        parent::__construct($app);

        $this->namespace = config('routes.namespace');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/routes.php' => config_path('routes.php')
            ], 'routes-config');
        }


        parent::boot();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/routes.php', 'routes');

        parent::register();
    }

    private function mapRoutes($path, $namespace = null, $prefix = null, RouteModuleConfig $config = null): void
    {
        $route = Route::namespace($namespace)->prefix($prefix)->middleware($config->getMiddleware());

        $config->getAs() && $route->as($config->getAs());
        $config->getDomain() && $route->domain($config->getDomain());
        $config->getWhere() && $route->where($config->getWhere());

        $route->group(base_path($path));
    }

    private function explodePath(string $path): Collection
    {
        preg_match_all("/[^\\\<>\/|\"]+/", $path, $pieces);
        return collect($pieces[0] ?? []);
    }

    private function pathWithout($path, $without): string
    {
        $path = $this->explodePath($path)->join(DIRECTORY_SEPARATOR);
        $without = $this->explodePath($without)->join(DIRECTORY_SEPARATOR);

        return $this->explodePath(str_replace($without, null, $path))->join(DIRECTORY_SEPARATOR);
    }

    private function normalizeNamespace(RouteModuleConfig $config, string $dirname): string
    {
        $namespace = $config->getNamespace();

        if ($config->extendNamespace()) {
            $namespace .= self::NAMESPACE_SEPARATOR . $dirname;
        }

        return $this
            ->explodePath($this->namespace . DIRECTORY_SEPARATOR . $namespace)
            ->map(fn($piece) => Str::ucfirst(Str::camel($piece)))
            ->join(self::NAMESPACE_SEPARATOR);
    }

    private function normalizePrefix(RouteModuleConfig $config, string $dirname): string
    {
        $prefix = $config->getPrefix();
        if ($config->extendPrefix()) {
            $prefix .= self::ROUTE_SEPARATOR . $dirname;
        }

        return $this
            ->explodePath($prefix)
            ->join(self::ROUTE_SEPARATOR);
    }

    public function map(): void
    {
        $modules = Arr::wrap(config('routes.modules'));

        foreach ($modules as $module) {
            $routeConfig = new RouteModuleConfig($module);

            $files = File::allFiles(base_path('routes' . DIRECTORY_SEPARATOR . $routeConfig->getDirectory()));

            foreach ($files as $file) {
                $path = $this->pathWithout($file->getRealPath(), base_path());
                $dirname = $this->pathWithout(File::dirname($path), 'routes' . DIRECTORY_SEPARATOR . $routeConfig->getDirectory());

                $this->mapRoutes(
                    $path,
                    $this->normalizeNamespace($routeConfig, $dirname),
                    $this->normalizePrefix($routeConfig, $dirname),
                    $routeConfig
                );
            }
        }
    }
}
