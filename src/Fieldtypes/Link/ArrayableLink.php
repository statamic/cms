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

    public function url()
    {
        $value = $this->value;

        if (is_object($this->value)) {
            $extra = $this->extra();
            if (in_array('select_across_sites', $extra) && $extra['select_across_sites']) {
                $value = $this->value->absoluteUrl();
            } else {
                $value = $this->value?->url();
            }
        }

        return $value;
    }
}
