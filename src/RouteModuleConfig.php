<?php

namespace GoldcarrotLaravel\Routes;

use GoldcarrotLaravel\Routes\Exceptions\InvalidConfigurationException;
use Illuminate\Support\Arr;

class RouteModuleConfig
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        if (!Arr::has($config, 'directory')) {
            throw new InvalidConfigurationException('Directory must be set in each module');
        }
    }

    public function extendNamespace(): bool
    {
        return (bool)Arr::get($this->config, 'extendNamespaceFromFolders', true);
    }

    public function extendPrefix(): bool
    {
        return (bool)Arr::get($this->config, 'extendPrefixFromFolders', true);
    }

    public function getDirectory()
    {
        return Arr::get($this->config, 'directory');
    }

    public function getNamespace()
    {
        return Arr::get($this->config, 'namespace');
    }

    public function getPrefix()
    {
        return Arr::get($this->config, 'prefix');
    }

    public function getWhere()
    {
        return Arr::get($this->config, 'where');
    }

    public function getMiddleware()
    {
        return Arr::get($this->config, 'middleware');
    }

    public function getDomain()
    {
        return Arr::get($this->config, 'domain');
    }

    public function getAs()
    {
        return Arr::get($this->config, 'as');
    }
}