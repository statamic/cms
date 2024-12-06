<?php

namespace Statamic\Data;

use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\BulkAugmentable;

class BulkAugmentor
{
    private $isTree = false;
    private $originalValues = [];
    private $augmentedValues = [];
    private ?array $keys = null;

    private function getAugmentationReference($item)
    {
        if ($item instanceof BulkAugmentable && $key = $item->getBulkAugmentationReferenceKey()) {
            return $key;
        }

        return 'Ref::'.get_class($item).spl_object_hash($item);
    }

    public static function make($items)
    {
        return (new static)->augment($items);
    }

    public static function tree($tree, $keys = null)
    {
        return (new static)
            ->withKeys($keys)
            ->augmentTree($tree);
    }

    public function withKeys(?array $keys)
    {
        $this->keys = $keys;

        return $this;
    }

    /**
     * @param  array<Augmentable>  $items
     * @return $this
     */
    private function augment($items)
    {
        $referenceKeys = [];
        $referenceFields = [];

        foreach ($items as $i => $item) {
            $reference = $this->getAugmentationReference($item);

            if (! $this->isTree) {
                $this->originalValues[$i] = $item;
            }

            if (array_key_exists($reference, $referenceKeys)) {
                continue;
            }

            $augmented = $item->augmented();
            $referenceKeys[$reference] = $this->keys ?? $augmented->keys();
            $referenceFields[$reference] = $augmented->blueprintFields();
        }

        foreach ($items as $i => $item) {
            $reference = $this->getAugmentationReference($item);
            $fields = $referenceFields[$reference];
            $keys = $referenceKeys[$reference];

            $this->augmentedValues[$i] = $item->toDeferredAugmentedArrayUsingFields($keys, $fields);
        }

        return $this;
    }

    private function augmentTree($tree)
    {
        $this->isTree = true;

        if (! $tree) {
            return $this;
        }

        $items = [];

        foreach ($tree as $i => $item) {
            $items[] = $item['page'];
            $this->originalValues[$i] = $item;
        }

        return $this->augment($items);
    }

    public function map(callable $callable)
    {
        $items = [];

        foreach ($this->originalValues as $i => $original) {
            $augmented = $this->augmentedValues[$i];

            $items[] = call_user_func_array($callable, [$original, $augmented, $i]);
        }

        return collect($items);
    }

    public function toArray()
    {
        return $this->augmentedValues;
    }
}
