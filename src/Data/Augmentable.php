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

        // Convert provided data into values.
        $values = collect($this->augmentedArrayData())->map(function ($value, $handle) use ($fields) {
            if (! $fields->has($handle)) {
                return $value;
            }

            return new Value($value, $handle, $fields->get($handle)->fieldtype(), $this);
        });

        // Any values that aren't in the data but are in the blueprint should have their defaults/nulls added.
        if ($blueprint) {
            foreach ($fields as $handle => $field) {
                if (! $values->has($handle)) {
                    $values[$handle] = new Value($field->defaultValue(), $handle, $field->fieldtype(), $this);
                }
            }
        }

        return $values->all();
    }

    public function augmentedArrayData()
    {
        return method_exists($this, 'values') ? $this->values() : $this->data();
    }
}
