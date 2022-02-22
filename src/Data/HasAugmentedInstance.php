<?php

namespace Statamic\Data;

use BadMethodCallException;
use Statamic\Contracts\Data\Augmented;
use Statamic\Facades\Compare;
use Statamic\Fields\Value;

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

    public function shallowAugmentedArrayKeys()
    {
        return ['id', 'title', 'api_url'];
    }

    public function __get($key)
    {
        $value = $this->augmentedValue($key);

        $value = $value instanceof Value ? $value->value() : $value;

        if (Compare::isQueryBuilder($value)) {
            $value = $value->get();
        }

        return $value;
    }

    public function __call($method, $args)
    {
        $value = $this->augmentedValue($method);

        $value = $value instanceof Value ? $value->value() : $value;

        if (Compare::isQueryBuilder($value)) {
            return $value;
        }

        throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', static::class, $method));
    }
}
