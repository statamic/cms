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
use Statamic\Fieldtypes\Link\ArrayableLink;

class AugmentedCollection extends Collection
{
    protected $shallowNesting = false;
    protected $shouldEvaluate = false;
    protected $withRelations = [];

    /**
     * The linked items that are currently being converted to an array. A link
     * back to one of them is represented shallowly, otherwise entries that link
     * to each other would be converted to arrays forever.
     */
    private static $expandingLinks = [];

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

    public function all()
    {
        return collect($this->items)
            ->map(fn ($item) => $item instanceof Value ? $item->resolve() : $item)
            ->all();
    }

    public function deferredAll()
    {
        return parent::all();
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

            if ($value instanceof ArrayableLink) {
                return $this->linkToArray($value);
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

    private function linkToArray(ArrayableLink $link)
    {
        $item = $link->value();

        if (! is_object($item)) {
            return $link->toArray();
        }

        $reference = method_exists($item, 'reference') ? $item->reference() : spl_object_id($item);

        if (in_array($reference, self::$expandingLinks, true)) {
            return (new self($link->jsonSerialize()))->withEvaluation()->toArray();
        }

        self::$expandingLinks[] = $reference;

        try {
            return (new self($link->toArray()))->withEvaluation()->toArray();
        } finally {
            array_pop(self::$expandingLinks);
        }
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
