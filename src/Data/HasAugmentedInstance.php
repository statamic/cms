<?php

namespace Statamic\Data;

use Statamic\Contracts\Data\Augmented;

trait HasAugmentedInstance
{
    public function augmentedValue($key)
    {
        return $this->augmented()->get($key);
    }

    public function toAugmentedCollection($keys = null)
    {
        return $this->augmented()->select($keys ?? $this->defaultAugmentedArrayKeys());
    }

    public function toAugmentedArray($keys = null)
    {
        return $this->toAugmentedCollection($keys)->all();
    }

    public function toShallowAugmentedCollection()
    {
        return $this->augmented()->select($this->shallowAugmentedArrayKeys())->withShallowNesting();
    }

    public function toShallowAugmentedArray()
    {
        return $this->toShallowAugmentedCollection()->all();
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
