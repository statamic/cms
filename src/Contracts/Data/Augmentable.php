<?php

namespace Statamic\Contracts\Data;

interface Augmentable
{
    public function augmentedValue($key);
    public function toAugmentedArray($keys = null);
}
