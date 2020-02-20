<?php

namespace Statamic\Fields;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class LabeledValue implements Arrayable, JsonSerializable
{
    protected $value;
    protected $label;

    public function __construct($value, $label)
    {
        $this->value = $value;
        $this->label = $label;
    }

    public function value()
    {
        return $this->value;
    }

    public function label()
    {
        return $this->label;
    }

    public function __toString()
    {
        return (string) $this->value ?? '';
    }

    public function toArray()
    {
        return [
            'key' => $this->value,
            'value' => $this->value,
            'label' => $this->label,
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
