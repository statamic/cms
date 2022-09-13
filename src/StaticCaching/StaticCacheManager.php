<?php

namespace Statamic\StaticCaching;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
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
            'ignore_query_strings' => $this->app['config']['statamic.static_caching.ignore_query_strings'] ?? false,
            'locale' => Site::current()->handle(),
        ]);
    }

    public function flush()
    {
        $this->driver()->flush();

        collect(Cache::get('nocache::urls', []))->each(function ($url) {
            Cache::forget('nocache::session.'.md5($url));
        });

        Cache::forget('nocache::urls');
    }

    public function nocacheJs(string $js)
    {
        $this->fileDriver()->setNocacheJs($js);
    }

    public function nocachePlaceholder(string $placeholder)
    {
        $this->fileDriver()->setNocachePlaceholder($placeholder);
    }

    public function includeJs()
    {
        $this->fileDriver()->includeJs();
    }

    private function fileDriver()
    {
        return ($driver = $this->driver()) instanceof FileCacher ? $driver : optional();
    }
}
