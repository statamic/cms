<?php

namespace Statamic\Search;

use Statamic\Contracts\Search\Searchable;
use Statamic\Search\Searchables\Providers;

class Search
{
    protected $indexes;

    public function __construct(IndexManager $indexes)
    {
        $this->indexes = $indexes;
    }

    public function indexes()
    {
        return $this->indexes->all();
    }

    public function index($index = null, $locale = null)
    {
        return $this->indexes->index($index, $locale);
    }

    public function in($index = null, $locale = null)
    {
        return $this->index($index, $locale);
    }

    public function extend($driver, $callback)
    {
        app(IndexManager::class)->extend($driver, $callback);
    }

    public function registerSearchableProvider($class)
    {
        app(Providers::class)->register($class);
    }

    public function __call($method, $parameters)
    {
        return $this->index()->$method(...$parameters);
    }

    public function updateWithinIndexes(Searchable $searchable)
    {
        $this->indexes()->each(function ($index) use ($searchable) {
            $this->updateWithinIndex($index, $searchable);
        });
    }

    private function updateWithinIndex(Index $index, Searchable $searchable): void
    {
        $shouldIndex = $index->shouldIndex($searchable);
        $exists = $index->exists();

        // The index already exists and the entry should be indexed: insert the entry
        // into the existing index.
        if ($shouldIndex && $exists) {
            $index->insert($searchable);

            return;
        }

        // The index does not already exist but the entry should be indexed: In this case, the autocreate_on_save
        // setting governs whether the index should be created and fully indexed or not.
        // Setting autocreate_on_save to false can be useful if the site contains a lot of data.
        if ($shouldIndex && ! $exists) {
            $config = $index->config();
            if ($config['autocreate_on_save'] ?? true) {
                $index->update();
            }

            return;
        }

        // The index already exists but the entry should not be indexed: delete the entry from the index.
        if ($exists) {
            $index->delete($searchable);
        }
    }

    public function deleteFromIndexes(Searchable $searchable)
    {
        $this->indexes()->each(function ($index) use ($searchable) {
            if ($index->exists()) {
                $index->delete($searchable);
            }
        });
    }
}
