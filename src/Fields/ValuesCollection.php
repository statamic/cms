<?php

namespace Statamic\Fields;

use Countable;
use IteratorAggregate;
use Traversable;

class ValuesCollection implements Countable, IteratorAggregate
{
    protected $instance;

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    public function getIterator(): Traversable
    {
        return $this->instance;
    }

    public function getProxiedInstance()
    {
        return $this->instance;
    }

    public function __call($method, $args)
    {
        return $this->instance->$method(...$args);
    }

    public function count(): int
    {
        return $this->instance->count();
    }
}
