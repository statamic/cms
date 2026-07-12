<?php

namespace Statamic\Fields;

class LabeledValue extends ArrayableString
{
    public function __construct($value, $label)
    {
        parent::__construct($value, ['label' => $label]);
    }

    public function label()
    {
        return $this->extra['label'];
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), ['key' => $this->value]);
    }
}
