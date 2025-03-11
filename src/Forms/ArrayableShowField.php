<?php

namespace Statamic\Forms;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Support\Arr;

class ArrayableShowField implements Arrayable, ArrayAccess
{
    public function __construct(protected $dottedShowFieldData)
    {
        //
    }

    public function toArray()
    {
        // TODO: This sidesteps the solution ArrayAccess is trying to solve though
        return Arr::undot($this->dottedShowFieldData);
    }

    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return Arr::get($this->dottedShowFieldData, $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        Arr::set($this->dottedShowFieldData, $offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        Arr::forget($this->dottedShowFieldData, $offset);
    }
}
