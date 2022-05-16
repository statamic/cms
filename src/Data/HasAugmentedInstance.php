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
        return $this->augmented()
            ->withRelations($this->defaultAugmentedRelations())
            ->select($keys ?? $this->defaultAugmentedArrayKeys());
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

    protected function defaultAugmentedRelations()
    {
        return [];
    }

    public function toEvaluatedAugmentedArray($keys = null)
    {
        $collection = $this->toAugmentedCollection($keys);

        // Can't just chain ->except() because it would return a new
        // collection and the existing 'withRelations' would be lost.
        if ($exceptions = $this->excludedEvaluatedAugmentedArrayKeys()) {
            $collection = $collection
                ->except($exceptions)
                ->withRelations($collection->getRelations());
        }

        return $collection->withEvaluation()->toArray();
    }

    protected function excludedEvaluatedAugmentedArrayKeys()
    {
        return null;
    }

    public function toArray()
    {
        return $this->toEvaluatedAugmentedArray();
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

    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->__get($key);
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        throw new \Exception('Method offsetSet is not currently supported.');
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        return ! is_null($this->offsetGet($key));
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        throw new \Exception('Method offsetUnset is not currently supported.');
    }

    public function __isset($key)
    {
        return $this->offsetExists($key);
    }
}
