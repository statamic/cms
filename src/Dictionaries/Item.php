<?php

namespace Statamic\Dictionaries;

use Statamic\Fields\LabeledValue;
use Statamic\Support\Arr;

class Item extends LabeledValue implements \ArrayAccess
{
    public function __construct($value, $label, array $extra)
    {
        parent::__construct($value, null);
        $this->extra = array_merge(
            $extra,
            ['label' => $label]
        );
    }

    public function data(): array
    {
        return Arr::except($this->extra, ['label']);
    }

    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data()[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->extra[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->extra[$offset]);
    }
}
