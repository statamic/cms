<?php

namespace Statamic\Fields;

class Value
{
    protected $raw;
    protected $handle;
    protected $fieldtype;

    public function __construct($value, $handle = null, $fieldtype = null)
    {
        $this->raw = $value;
        $this->handle = $handle;
        $this->fieldtype = $fieldtype;
    }

    public function raw()
    {
        return $this->raw;
    }

    public function value()
    {
        if (! $this->fieldtype) {
            return $this->raw;
        }

        return $this->fieldtype->augment($this->raw);
    }

    public function __toString()
    {
        return (string) $this->value();
    }
}
