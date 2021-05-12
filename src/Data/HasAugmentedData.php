<?php

namespace Statamic\Data;

use Statamic\Contracts\Data\Augmented;

trait HasAugmentedData
{
    use HasAugmentedInstance;

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedData($this, $this->augmentedArrayData());
    }

    public function augmentedArrayData()
    {
        return method_exists($this, 'values') ? $this->values() : $this->data();
    }
}
