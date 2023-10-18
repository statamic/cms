<?php

namespace Statamic\Search;

use Statamic\Contracts\Search\Searchable;
use Statamic\Search\Jobs\UpdateWithinIndexes;
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
        UpdateWithinIndexes::dispatch($searchable);
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
