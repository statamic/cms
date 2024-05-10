<?php

namespace Statamic\Contracts\Data;

interface Augmentable extends \JsonSerializable
{
    public function augmented(): Augmented;

    public function augmentedValue($key);

    public function toAugmentedArray($keys = null);

    public function toDeferredAugmentedArray($keys = null);

    public function toDeferredAugmentedArrayUsingFields($keys, $fields);

    public function toAugmentedCollection($keys = null);

    public function toShallowAugmentedArray();

    public function toShallowAugmentedCollection();

    public function toEvaluatedAugmentedArray($keys = null);
}
