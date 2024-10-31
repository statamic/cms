<?php

namespace Statamic\Fields;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Statamic\Contracts\Support\Boolable;

class ArrayableString implements Arrayable, ArrayAccess, Boolable, JsonSerializable
{
    protected $value;
    protected $extra;

    public function __construct($value, array $extra = [])
    {
        $this->value = $value;
        $this->extra = $extra;
    }

    public function value()
    {
        return $this->value;
    }

    public function extra()
    {
        return (array) $this->extra;
    }

    public function toBool(): bool
    {
        return (bool) $this->value;
    }

    public function __toString()
    {
        return (string) $this->value ?? '';
    }

    public function toArray()
    {
        return array_merge(['value' => $this->value], $this->extra);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    #[\ReturnTypeWillChange]
    public function offsetExists(mixed $offset)
    {
        $value = $this->toArray();

        if (! is_array($value)) {
            return false;
        }

        return array_key_exists($offset, $value);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $offset)
    {
        return $this->toArray()[$offset];
    }

    #[\ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value)
    {

    }

    #[\ReturnTypeWillChange]
    public function offsetUnset(mixed $offset)
    {
    }
}
