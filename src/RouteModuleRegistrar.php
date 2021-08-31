<?php

namespace GoldcarrotLaravel\Routes;

use GoldcarrotLaravel\Routes\Helpers\PathHelper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class RouteModuleRegistrar
{
    private static $rootNamespace;

    public static function setRootNamespace($namespace)
    {
        static::$rootNamespace = $namespace;
        return app(static::class);
    }

    public function register($config = [])
    {
        foreach ($config as $moduleConfig) {
            $this->registerModule(new RouteModuleConfig($moduleConfig));
        }
    }

    public function registerModule(RouteModuleConfig $moduleConfig)
    {
        $files = File::allFiles(base_path('routes' . DIRECTORY_SEPARATOR . $moduleConfig->getDirectory()));

        foreach ($files as $file) {
            $path = PathHelper::removePart($file->getRealPath(), base_path());
            $dirname = PathHelper::removePart(File::dirname($path), 'routes' . DIRECTORY_SEPARATOR . $moduleConfig->getDirectory());

            $this->mapRoutes(
                $path,
                PathHelper::normalizeNamespace($moduleConfig, $dirname, static::$rootNamespace),
                PathHelper::normalizePrefix($moduleConfig, $dirname),
                $moduleConfig
            );
        }
    }

    private function mapRoutes($path, $namespace = null, $prefix = null, RouteModuleConfig $config = null): void
    {
        $route = Route::namespace($namespace)->prefix($prefix)->middleware($config->getMiddleware());

        $config->getAs() && $route->as($config->getAs());
        $config->getDomain() && $route->domain($config->getDomain());
        $config->getWhere() && $route->where($config->getWhere());

        $route->group(base_path($path));
    }

}