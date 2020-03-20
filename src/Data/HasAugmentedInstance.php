<?php

namespace Statamic\Data;

use Statamic\Contracts\Data\Augmented;

trait HasAugmentedInstance
{
    public function augmentedValue($key)
    {
        return $this->augmented()->get($key);
    }

    public function toAugmentedValues($keys = null)
    {
        return $this->augmented()->select($keys ?? $this->defaultAugmentedArrayKeys());
    }

    public function toAugmentedArray($keys = null)
    {
        return $this->toAugmentedValues($keys)->all();
    }

    public function toShallowAugmentedValues()
    {
        return $this->augmented()->select($this->shallowAugmentedArrayKeys());
    }

    public function toShallowAugmentedArray()
    {
        return $this->toShallowAugmentedValues()->all();
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

    protected function shallowAugmentedArrayKeys()
    {
        return ['id', 'title', 'api_url'];
    }
}
