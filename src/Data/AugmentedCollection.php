<?php

namespace Statamic\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use JsonSerializable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Fields\Value;

class AugmentedCollection extends Collection
{
    public function jsonSerialize()
    {
        return array_map(function ($value) {
            if ($value instanceof Value) {
                $value = $value->shallow();
            }

            if ($value instanceof Augmentable) {
                return $value->toShallowAugmentedArray();
            }

            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            }

            return $value;
        }, $this->all());
    }
}
