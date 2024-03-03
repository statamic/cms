<?php

namespace Statamic\Support;

class Blink
{
    protected $stores = [];

    public function store($name = 'default')
    {
        return $this->stores[$name] = $this->stores[$name] ?? new BlinkWrapper();
    }

    public function __call($method, $args)
    {
        return $this->store()->$method(...$args);
    }
}
