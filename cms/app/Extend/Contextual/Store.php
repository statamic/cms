<?php

namespace Statamic\Extend\Contextual;

/**
 * A singleton class that acts as a container for all the
 * ContextualObject classes for each addon at any given time.
 */
class Store
{
    private $store;

    public function __construct()
    {
        $this->store = collect();
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->store, $method], $args);
    }
}
