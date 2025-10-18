<?php

namespace Statamic\Fieldtypes\Link;

use Illuminate\Support\Arr;
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

    public function url()
    {
        if (! is_object($this->value)) {
            return $this->value;
        }

        if (Arr::get($this->extra(), 'select_across_sites')) {
            return $this->value->absoluteUrl();
        }

        return $this->value?->url();
    }
}
