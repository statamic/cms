<?php

namespace Statamic\Data;

trait HasAugmentedData
{
    use HasAugmentedInstance;

    public function newAugmentedInstance()
    {
        return new AugmentedArray($this, $this->augmentedArrayData());
    }

    public function augmentedArrayData()
    {
        return method_exists($this, 'values') ? $this->values() : $this->data();
    }
}
