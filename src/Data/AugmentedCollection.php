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
    protected $shallowNesting = false;
    protected $shouldEvaluate = false;

    /**
     * Enables shallow augmentation on nested values when
     * converting to array or JSON serializing.
     */
    public function withShallowNesting()
    {
        $this->shallowNesting = true;

        return $this;
    }

    public function hasShallowNesting()
    {
        return $this->shallowNesting;
    }

    public function withEvaluation()
    {
        $this->shouldEvaluate = true;

        return $this;
    }

    public function withoutEvaluation()
    {
        $this->shouldEvaluate = false;

        return $this;
    }

    public function toArray()
    {
        return $this->map(function ($value) {
            if ($this->shallowNesting && $value instanceof Value) {
                $value = $value->shallow();
            }

            if ($this->shallowNesting && $value instanceof Augmentable) {
                return $value->toShallowAugmentedArray();
            }

            if ($this->shouldEvaluate && $value instanceof Value) {
                $value = $value->value();
            }

            $value = $value instanceof Arrayable ? $value->toArray() : $value;

            if (is_array($value) || $value instanceof Collection) {
                $value = (new self($value))->withEvaluation()->toArray();
            }

            return $value;
        })->all();
    }

    public function jsonSerialize()
    {
        return array_map(function ($value) {
            if ($this->shallowNesting && $value instanceof Augmentable) {
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
