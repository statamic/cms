<?php

namespace Statamic\Fields;

use ArrayAccess;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Query\Builder as IlluminateQueryBuilder;
use Statamic\Contracts\Query\Builder as StatamicQueryBuilder;

class Values implements ArrayAccess
{
    protected $instance;

    public function __construct($instance)
    {
        if (is_array($instance)) {
            $instance = collect($instance);
        }

        $this->instance = $instance;
    }

    public function getProxiedInstance()
    {
        return $this->instance;
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->getProxiedCollection()->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        $value = $this->getProxiedCollection()->get($offset);

        return $value instanceof Value ? $value->value() : $value;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception('Cannot set values by array access.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new Exception('Cannot unset values by array access.');
    }

    public function raw($key)
    {
        $value = $this->getProxiedCollection()->get($key);

        return $value instanceof Value ? $value->raw() : $value;
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    public function __set($key, $value)
    {
        throw new Exception('Cannot set values by property access.');
    }

    public function __call($method, $args)
    {
        $this->instance = $this->instance->$method(...$args);

        return $this;
    }

    private function getProxiedCollection()
    {
        if (
            $this->instance instanceof StatamicQueryBuilder
            || $this->instance instanceof IlluminateQueryBuilder
            || $this->instance instanceof EloquentQueryBuilder
        ) {
            $this->instance = $this->instance->get();
        }

        return $this->instance;
    }
}
