<?php

namespace Statamic\Search;

use Algolia\AlgoliaSearch\SearchClient;
use Statamic\Search\Algolia\Index as AlgoliaIndex;
use Statamic\Search\Comb\Index as CombIndex;
use Statamic\Support\Manager;

class IndexManager extends Manager
{
    protected function invalidImplementationMessage($name)
    {
        return "Search index [{$name}] is not defined.";
    }

    public function all()
    {
        return collect($this->app['config']['statamic.search.indexes'])->map(function ($config, $name) {
            return $this->index($name);
        });
    }

    public function index($name = null)
    {
        return $this->driver($name);
    }

    public function getDefaultDriver()
    {
        return $this->app['config']['statamic.search.default'];
    }

    public function createLocalDriver(array $config, $name)
    {
        return new CombIndex($name, $config);
    }

    public function createAlgoliaDriver(array $config, $name)
    {
        $credentials = $config['credentials'];

        $client = SearchClient::create($credentials['id'], $credentials['secret']);

        return new AlgoliaIndex($client, $name, $config);
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
