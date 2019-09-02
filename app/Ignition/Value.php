<?php

namespace Statamic\Ignition;

use Statamic\Fields\Value as FieldValue;

class Value
{
    protected $raw;
    protected $augmented;
    protected $fieldtype;

    public function __construct(FieldValue $value)
    {
        $this->raw = $value->raw();
        $this->augmented = $value->value();
        $this->fieldtype = optional($value->fieldtype())->handle();
    }
}
