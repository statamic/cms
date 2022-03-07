<?php

namespace Statamic\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Query\Builder as IlluminateQueryBuilder;
use Illuminate\Support\Collection;
use JsonSerializable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Query\Builder as StatamicQueryBuilder;
use Statamic\Fields\Value;

class AugmentedCollection extends Collection
{
    protected $shallowNesting = false;
    protected $shouldEvaluate = false;
    protected $withRelations = [];

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
            if ($value instanceof Value && $value->isRelationship()) {
                $value = in_array($value->handle(), $this->withRelations)
                    ? ($this->shallowNesting ? $value->shallow() : $value->value())
                    : (array) $value->raw();
            }

            if ($this->shallowNesting && $value instanceof Value) {
                $value = $value->shallow();
            }

            if ($this->shallowNesting && $value instanceof Augmentable) {
                return $value->toShallowAugmentedArray();
            }

            if ($this->shouldEvaluate && $value instanceof Value) {
                $value = $value->value();
            }

            if ($this->isQueryBuilder($value)) {
                $value = $value->get();
            }

            $value = $value instanceof Arrayable ? $value->toArray() : $value;

            if (is_array($value) || $value instanceof Collection) {
                $value = (new self($value))->withEvaluation()->toArray();
            }

            return $value;
        })->all();
    }

    public function jsonSerialize(): array
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

    private function isQueryBuilder($value)
    {
        return $value instanceof StatamicQueryBuilder
            || $value instanceof IlluminateQueryBuilder;
    }

    public function withRelations($relations)
    {
        $this->withRelations = $relations;

        return $this;
    }

    public function getRelations()
    {
        return $this->withRelations;
    }
}
