<?php

namespace Statamic\Fieldtypes\Link;

use Statamic\Fields\ArrayableString;

class ArrayableLink extends ArrayableString
{
    public function __toString()
    {
        return (string) $this->url();
    }

    public function toArray()
    {
        return is_object($this->value)
            ? $this->value->toAugmentedArray()
            : ['url' => $this->url()];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->url(); // Use a string for backwards compatibility in the REST API, etc.
    }

    private function url()
    {
        return is_object($this->value) ? $this->value?->url() : $this->value;
    }
}
