<?php

namespace Statamic\Fields;

use ArrayAccess;
use BadMethodCallException;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;
use JsonSerializable;
use Statamic\Contracts\GraphQL\ResolvesValues as ResolvesGqlValues;
use Statamic\Facades\Compare;
use Traversable;

class Values implements ArrayAccess, Arrayable, IteratorAggregate, JsonSerializable, ResolvesGqlValues
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

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return $this->getProxiedInstance()->has($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        $value = $this->getNormalizedValueFromProxiedCollection($offset);

        if (Compare::isQueryBuilder($value)) {
            return $value->get();
        }

        return $value;
    }

    private function getNormalizedValueFromProxiedCollection($key)
    {
        $value = $this->getProxiedInstance()->get($key);

        return $value instanceof Value ? $value->value() : $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        throw new Exception('Cannot set values by array access.');
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        throw new Exception('Cannot unset values by array access.');
    }

    public function getIterator(): Traversable
    {
        return $this->getProxiedInstance();
    }

    public function raw($key)
    {
        $value = $this->getProxiedInstance()->get($key);

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
        if ($this->getProxiedInstance()->has($method)) {
            $value = $this->getNormalizedValueFromProxiedCollection($method);

            if (Compare::isQueryBuilder($value)) {
                return $value;
            }
        }

        throw new BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $method));
    }

    public function toArray()
    {
        return $this->getProxiedInstance()->toArray();
    }

    public function all()
    {
        return $this->getProxiedInstance()->all();
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->all();
    }

    public function resolveGqlValue($field)
    {
        return $this->$field;
    }

    public function resolveRawGqlValue($field)
    {
        return $this->raw($field);
    }
}
