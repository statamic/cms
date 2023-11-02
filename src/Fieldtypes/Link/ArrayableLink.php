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
        if (is_string($this->value)) {
            return ['url' => $this->value];
        }

        return $this->value?->toAugmentedArray();
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->url(); // Use a string for backwards compatibility in the REST API, etc.
    }

    private function url()
    {
        if (is_string($this->value)) {
            return $this->value;
        }

        return $this->value?->url();
    }
}
