<?php

namespace Statamic\Search;

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
}
