<?php

namespace Statamic\Data;

use Illuminate\Support\Collection;

trait HasAugmentedData
{
    public function toAugmentedArray()
    {
        $arr = $this->augmentedArrayData();

        return $arr instanceof Collection ? $arr->all() : $arr;
    }

    public function augmentedValue($key)
    {
        return $this->toAugmentedArray()[$key];
    }

    public function augmentedArrayData()
    {
        return method_exists($this, 'values') ? $this->values() : $this->data();
    }
}
