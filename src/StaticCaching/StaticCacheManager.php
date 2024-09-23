<?php

namespace Statamic\StaticCaching;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Statamic\Events\StaticCacheCleared;
use Statamic\Facades\Site;
use Statamic\StaticCaching\Cachers\ApplicationCacher;
use Statamic\StaticCaching\Cachers\FileCacher;
use Statamic\StaticCaching\Cachers\NullCacher;
use Statamic\StaticCaching\Cachers\Writer;
use Statamic\StaticCaching\NoCache\DatabaseRegion;
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
        return new FileCacher(new Writer($config['permissions'] ?? []), $this->cacheStore(), $config);
    }

    public function createApplicationDriver(array $config)
    {
        return new ApplicationCacher($this->app[Repository::class], $config);
    }

    public function cacheStore()
    {
        return Cache::store($this->hasCustomStore() ? 'static_cache' : null);
    }

    private function hasCustomStore(): bool
    {
        return config()->has('cache.stores.static_cache');
    }

    protected function getConfig($name)
    {
        if (! $config = $this->app['config']["statamic.static_caching.strategies.$name"]) {
            return null;
        }

        return array_merge($config, [
            'exclude' => $this->app['config']['statamic.static_caching.exclude'] ?? [],
            'ignore_query_strings' => $this->app['config']['statamic.static_caching.ignore_query_strings'] ?? false,
            'allowed_query_strings' => $this->app['config']['statamic.static_caching.allowed_query_strings'] ?? [],
            'disallowed_query_strings' => $this->app['config']['statamic.static_caching.disallowed_query_strings'] ?? [],
            'locale' => Site::current()->handle(),
        ]);
    }

    public function flush()
    {
        $this->driver()->flush();

        $this->flushNocache();

        if ($this->hasCustomStore()) {
            $this->cacheStore()->flush();
        }

        StaticCacheCleared::dispatch();
    }

    private function flushNocache()
    {
        if (config('statamic.static_caching.nocache', 'cache') === 'database') {
            DatabaseRegion::truncate();

            return;
        }

        // No need to do any looping if there's a custom
        // store because the entire store will be flushed.
        if ($this->hasCustomStore()) {
            return;
        }

        collect($this->cacheStore()->get('nocache::urls', []))->each(function ($url) {
            $session = $this->cacheStore()->get($sessionKey = 'nocache::session.'.md5($url));
            collect($session['regions'] ?? [])->each(fn ($region) => $this->cacheStore()->forget('nocache::region.'.$region));
            $this->cacheStore()->forget($sessionKey);
        });

        $this->cacheStore()->forget('nocache::urls');
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
