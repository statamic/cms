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
        return $this->augmented()->select($keys ?? $this->defaultAugmentedArrayKeys());
    }

    public function augmented()
    {
        return $this->newAugmentedInstance();
    }

    abstract public function newAugmentedInstance(): Augmented;

    protected function defaultAugmentedArrayKeys()
    {
        return null;
    }
}
