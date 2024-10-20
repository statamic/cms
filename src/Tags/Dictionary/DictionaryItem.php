<?php

namespace Statamic\Tags\Dictionary;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Statamic\Data\ContainsSupplementalData;

class DictionaryItem implements Arrayable, ArrayAccess
{
    use ContainsSupplementalData;

    public function __construct(public array $data)
    {
    }

    public function get($key, $default = null)
    {
        return Arr::get($this->toArray(), $key, $default);
    }

    public function toArray()
    {
        return array_merge($this->data, $this->supplements ?? []);
    }

    public function __get($key)
    {
        return $this->toArray()[$key] ?? null;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists(mixed $offset)
    {
        return array_key_exists($offset, $this->toArray());
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
