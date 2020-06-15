<?php

namespace Statamic\StaticCaching;

use Illuminate\Cache\Repository;
use Statamic\Facades\Site;
use Statamic\StaticCaching\Cachers\ApplicationCacher;
use Statamic\StaticCaching\Cachers\FileCacher;
use Statamic\StaticCaching\Cachers\NullCacher;
use Statamic\StaticCaching\Cachers\Writer;
use Statamic\Support\Manager;

class StaticCacheManager extends Manager
{
    protected function invalidImplementationMessage($name)
    {
        return "Static cache strategy [{$name}] is not defined.";
    }

    public function getDefaultDriver()
    {
        return $this->app['config']['statamic.static_caching.strategy'] ?? 'null';
    }

    public function createNullDriver()
    {
        return new NullCacher;
    }

    public function createFileDriver(array $config)
    {
        return new FileCacher(new Writer, $this->app[Repository::class], $config);
    }

    public function createApplicationDriver(array $config)
    {
        return new ApplicationCacher($this->app[Repository::class], $config);
    }

    protected function getConfig($name)
    {
        if (! $config = $this->app['config']["statamic.static_caching.strategies.$name"]) {
            return null;
        }

        return array_merge($config, [
            'exclude' => $this->app['config']['statamic.static_caching.exclude'] ?? [],
            'base_url' => $this->app['request']->root(),
            'locale' => Site::current()->handle(),
        ]);
    }
}
