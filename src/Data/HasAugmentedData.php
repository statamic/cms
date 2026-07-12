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
        if (method_exists($this, 'values')) {
            return $this->values();
        }

        if (method_exists($this, 'data')) {
            return $this->data();
        }

        throw new \Exception('Augmentable object must have a values() or data() method, or override the augmentedArrayData() method.');
    }
}
