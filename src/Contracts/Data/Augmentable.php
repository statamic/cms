<?php

namespace Statamic\Contracts\Data;

interface Augmentable
{
    public function augmentedValue($key);

    public function toAugmentedArray($keys = null);

    public function toAugmentedCollection($keys = null);

    public function toShallowAugmentedArray();

    public function toShallowAugmentedCollection();

    public function toEvaluatedAugmentedArray($keys = null);
}
