<?php

namespace Statamic\Fields;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class ArrayableString implements Arrayable, JsonSerializable
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
}
