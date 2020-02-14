<?php

namespace Statamic\Support;

use Spatie\Blink\Blink as SpatieBlink;

class Blink
{
    protected $stores = [];

    public function store($name = 'default')
    {
        return $this->stores[$name] = $this->stores[$name] ?? new SpatieBlink;
    }

    public function __call($method, $args)
    {
        return $this->store()->$method(...$args);
    }
}
