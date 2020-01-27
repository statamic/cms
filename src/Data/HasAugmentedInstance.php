<?php

namespace Statamic\Data;

use Statamic\Contracts\Data\Augmented;

trait HasAugmentedInstance
{
    public function augmentedValue($key)
    {
        return $this->augmented()->get($key);
    }

    public function toAugmentedArray($keys = null)
    {
        return $this->augmented()->select($keys);
    }

    public function augmented()
    {
        return $this->newAugmentedInstance();
    }

    abstract public function newAugmentedInstance(): Augmented;
}
