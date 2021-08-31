<?php

namespace GoldcarrotLaravel\Routes\Helpers;

use GoldcarrotLaravel\Routes\RouteModuleConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PathHelper
{
    public const ROUTE_SEPARATOR = '/';
    public const NAMESPACE_SEPARATOR = "\\";

    public static function explode(string $path): Collection
    {
        preg_match_all("/[^\\\<>\/|\"]+/", $path, $pieces);
        return collect($pieces[0] ?? []);
    }

    public static function removePart($source, $part): string
    {
        $path = self::explode($source)->join(DIRECTORY_SEPARATOR);
        $without = self::explode($part)->join(DIRECTORY_SEPARATOR);

        return self::explode(str_replace($without, null, $path))->join(DIRECTORY_SEPARATOR);
    }

    public static function normalizeNamespace(RouteModuleConfig $config, string $dirname, string $rootNamespace = null): string
    {
        $namespace = $config->getNamespace();

        if ($config->extendNamespace()) {
            $namespace .= self::NAMESPACE_SEPARATOR . $dirname;
        }

        return self::explode($rootNamespace ? ($rootNamespace . DIRECTORY_SEPARATOR . $namespace) : $namespace)
            ->map(function ($piece) {
                return Str::ucfirst(Str::camel($piece));
            })
            ->join(self::NAMESPACE_SEPARATOR);
    }


    public static function normalizePrefix(RouteModuleConfig $config, string $dirname): string
    {
        $prefix = $config->getPrefix();

        if ($config->extendPrefix()) {
            $prefix .= self::ROUTE_SEPARATOR . $dirname;
        }

        return self::explode($prefix)
            ->map(function ($piece) {
                return Str::snake($piece, '-');
            })
            ->join(self::ROUTE_SEPARATOR);
    }
}