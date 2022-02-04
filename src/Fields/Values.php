<?php

namespace Statamic\Fields;

use ArrayAccess;
use BadMethodCallException;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Query\Builder as IlluminateQueryBuilder;
use Statamic\Contracts\Query\Builder as StatamicQueryBuilder;

class Values implements ArrayAccess
{
    protected $instance;
    protected $builders = [];

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
        $value = $this->getNormalizedValueFromProxiedCollection($offset);

        if ($this->isQueryBuilder($value)) {
            return $this->getQueryBuilderResults($value, $offset);
        }

        return $value;
    }

    private function getQueryBuilderResults($query, $key)
    {
        // Store the results of the query builder so we don't have to run it again.
        if (isset($this->builders[$key])) {
            return $this->builders[$key];
        }

        $instance = collect($query->get()->toAugmentedCollection())->mapInto(self::class);

        return $this->builders[$key] = new ValuesCollection($instance);
    }

    private function getNormalizedValueFromProxiedCollection($key)
    {
        $value = $this->getProxiedCollection()->get($key);

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
        if ($this->getProxiedCollection()->has($method)) {
            $value = $this->getNormalizedValueFromProxiedCollection($method);

            if ($this->isQueryBuilder($value)) {
                return new ValuesQueryBuilder($value);
            }
        }

        throw new BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $method));
    }

    private function isQueryBuilder($value)
    {
        return $value instanceof StatamicQueryBuilder
            || $value instanceof IlluminateQueryBuilder
            || $value instanceof EloquentQueryBuilder;
    }

    private function getProxiedCollection()
    {
        return $this->instance;
    }
}
