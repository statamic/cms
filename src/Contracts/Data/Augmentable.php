<?php

namespace Statamic\Contracts\Data;

interface Augmentable
{
    public function augmentedValue($key);
    public function toAugmentedArray($keys = null);
    public function toAugmentedValues($keys = null);
    public function toShallowAugmentedArray();
    public function toShallowAugmentedValues();
}
