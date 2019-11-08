<?php

namespace Statamic\Data;

use Statamic\Fields\Value;

trait Augmentable
{
    public function augment($key)
    {
        return $this->toAugmentedArray()[$key];
    }

    public function toAugmentedArray()
    {
        $fields = (method_exists($this, 'blueprint') ? $this->blueprint() : false)
            ? $this->blueprint()->fields()->all()
            : collect();

        return collect($this->augmentedArrayData())->map(function ($value, $handle) use ($fields) {
            return new Value($value, $handle, optional($fields->get($handle))->fieldtype(), $this);
        })->all();
    }

    public function augmentedArrayData()
    {
        return $this->toArray();
    }
}
