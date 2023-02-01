<?php

namespace Statamic\Search;

use Algolia\AlgoliaSearch\SearchClient;
use InvalidArgumentException;
use Statamic\Facades\Site;
use Statamic\Search\Algolia\Index as AlgoliaIndex;
use Statamic\Search\Comb\Index as CombIndex;
use Statamic\Search\Null\NullIndex;
use Statamic\Support\Manager;

class IndexManager extends Manager
{
    protected function invalidImplementationMessage($name)
    {
        return "Search index [{$name}] is not defined.";
    }

    public function all()
    {
        return collect($this->app['config']['statamic.search.indexes'])->flatMap(function ($config, $name) {
            $sites = $config['sites'] ?? null;

            if ($sites === 'all') {
                $sites = Site::all()->map->handle()->values()->all();
            }

            if ($sites) {
                return collect($sites)
                    ->map(fn ($site) => $this->index($name, $site))
                    ->all();
            }

            return [$this->index($name)];
        })->keyBy->name();
    }

    public function index($name = null, $locale = null)
    {
        return $this->driver($name, $locale);
    }

    public function driver($name = null, $locale = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        $locale = $locale ?: Site::current()->handle();

        $handle = $name.'_'.$locale;

        if ($this->drivers[$handle] ?? null) {
            return $this->drivers[$handle];
        }

        $driver = $this->resolveLocalized($name, $locale);

        return $this->drivers[$handle] = $driver;
    }

    protected function resolveLocalized($name, $locale)
    {
        $config = $this->getConfig($name);

        if ($name === 'null') {
            return $this->createNullDriver($config, $name, $locale);
        }

        if (is_null($config)) {
            throw new InvalidArgumentException($this->invalidImplementationMessage($name));
        }

        $sites = $config['sites'] ?? null;

        if ($sites === 'all') {
            $sites = Site::all()->map->handle()->values()->all();
        }

        if ($sites && ! in_array($locale, $sites)) {
            throw new InvalidArgumentException("Search index [$name] has not been configured for the [$locale] site.");
        }

        if (! $sites) {
            $locale = null;
        }

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callLocalizedCustomCreator($config, $name, $locale);
        } else {
            $driverMethod = 'create'.camel_case($config['driver']).'Driver';

            if (method_exists($this, $driverMethod)) {
                return $this->{$driverMethod}($config, $name, $locale);
            } else {
                throw new InvalidArgumentException($this->invalidDriverMessage($config['driver'], $name));
            }
        }
    }

    public function getDefaultDriver()
    {
        return $this->app['config']['statamic.search.default'];
    }

    public function createNullDriver(array $config, $name, $locale)
    {
        return new NullIndex($name, $config, $locale);
    }

    public function createLocalDriver(array $config, $name, $locale)
    {
        return new CombIndex($name, $config, $locale);
    }

    public function createAlgoliaDriver(array $config, $name, $locale)
    {
        $credentials = $config['credentials'];

        $client = SearchClient::create($credentials['id'], $credentials['secret']);

        return new AlgoliaIndex($client, $name, $config, $locale);
    }

    protected function callLocalizedCustomCreator(array $config, string $name, string $locale)
    {
        return $this->customCreators[$config['driver']]($this->app, $config, $name, $locale);
    }

    protected function getConfig($name)
    {
        $config = $this->app['config'];

        if (! $index = $config["statamic.search.indexes.$name"]) {
            return null;
        }

        return array_merge(
            $config['statamic.search.defaults'] ?? [],
            $config["statamic.search.drivers.{$index['driver']}"] ?? [],
            $index
        );
    }
}
