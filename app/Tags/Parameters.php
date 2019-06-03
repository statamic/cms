<?php

namespace Statamic\Tags;

use ArrayAccess;
use ArrayIterator;
use Statamic\API\Arr;
use Statamic\API\Str;
use IteratorAggregate;

class Parameters implements ArrayAccess, IteratorAggregate
{
    protected $parameters = [];

    public function __construct($parameters, $context)
    {
        $this->parameters = $this->initialize($parameters, $context);
    }

    public function all()
    {
        return $this->parameters;
    }

    public function get($key, $default = null)
    {
        return Arr::getFirst($this->parameters, Arr::wrap($key), $default);
    }

    public function has($key)
    {
        return isset($this->parameters[$key]);
    }

    public function remove($key)
    {
        unset($this->parameters[$key]);
    }

    public function offsetExists($key)
    {
        return $this->has($key);
    }

    public function offsetGet($key)
    {
        return $this->get($key);
    }

    public function offsetSet($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    public function explode($key, $default = null)
    {
        if (! $value = $this->get($key)) {
            return $default;
        }

        return explode('|', $value);
    }

    public function bool($key, $default = false)
    {
        if (! $value = $this->get($key)) {
            return $default;
        }

        return (bool) $value;
    }

    public function int($key, $default = 0)
    {
        if (! $value = $this->get($key)) {
            return $default;
        }

        return (int) $value;
    }

    public function float($key, $default = 0.0)
    {
        if (! $value = $this->get($key)) {
            return is_int($default) ? (float) $default : $default;
        }

        return (float) $value;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->parameters);
    }

    protected function initialize($parameters, $context)
    {
        return collect($parameters)->mapWithKeys(function ($value, $key) use ($context) {
            // Values in parameters prefixed with a colon should be treated as the corresponding
            // field's value in the context. If it doesn't exist, the value remains the literal.
            if (Str::startsWith($key, ':')) {
                $key = substr($key, 1);
                $value = $context[$value] ?? $value;
            }

            if ($value === 'true') {
                $value = true;
            }

            if ($value === 'false') {
                $value = false;
            }

            return [$key => $value];
        })->all();
    }
}