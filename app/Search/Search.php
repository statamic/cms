<?php

namespace Statamic\Search;

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

    public function index($index = null)
    {
        return $this->indexes->index($index);
    }

    public function in($index = null)
    {
        return $this->index($index);
    }

    public function clearIndex($index = null)
    {
        return $this->index($index)->clear();
    }

    public function indexExists($index = null)
    {
        return $this->index($index)->exists();
    }

    public function extend($driver, $callback)
    {
        app(IndexManager::class)->extend($driver, $callback);
    }

    public function __call($method, $parameters)
    {
        return $this->index()->$method(...$parameters);
    }
}
