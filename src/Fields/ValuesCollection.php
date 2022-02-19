<?php

namespace Statamic\Fields;

use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

class ValuesCollection implements Countable, IteratorAggregate, JsonSerializable
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

    public function __toString()
    {
        return (string) $this->instance;
    }

    public function jsonSerialize()
    {
        return $this->instance->jsonSerialize();
    }
}
