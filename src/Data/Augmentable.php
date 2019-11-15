<?php

namespace Statamic\Data;

use Statamic\Fields\Value;

trait Augmentable
{
    public function toAugmentedArray()
    {
        if (method_exists($this, 'blueprint') && ($blueprint = $this->blueprint())) {
            $fields = $blueprint->fields()->all();
        } else {
            $fields = collect();
        }

        return collect($this->augmentedArrayData())->map(function ($value, $handle) use ($fields) {
            return new Value($value, $handle, optional($fields->get($handle))->fieldtype(), $this);
        })->all();
    }

    public function augmentedArrayData()
    {
        return $this->toArray();
    }
}
