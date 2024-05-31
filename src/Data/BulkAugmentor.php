<?php

namespace Statamic\Data;

use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\BulkAugmentable;

class BulkAugmentor
{
    private $isTree = false;
    private $originalValues = [];
    private $augmentedValues = [];
    private $keyResolver;

    private function getAugmentationReference($item)
    {
        if ($item instanceof BulkAugmentable && $key = $item->getBulkAugmentationReferenceKey()) {
            return $key;
        }

        return 'Ref::'.get_class($item).spl_object_hash($item);
    }

    public static function make($items, $keyResolver = null)
    {
        return (new static)->resolveKeysWith($keyResolver)->augment($items);
    }

    public static function tree($tree, $keyResolver = null)
    {
        return (new static)->resolveKeysWith($keyResolver)->augmentTree($tree);
    }

    public function resolveKeysWith($callback)
    {
        $this->keyResolver = $callback;

        return $this;
    }

    /**
     * @param  array<Augmentable>  $items
     * @return $this
     */
    private function augment($items)
    {
        $count = count($items);

        $referenceKeys = [];
        $referenceFields = [];

        for ($i = 0; $i < $count; $i++) {
            $item = $items[$i];
            $reference = $this->getAugmentationReference($item);

            if (! $this->isTree) {
                $this->originalValues[$i] = $item;
            }

            if (array_key_exists($reference, $referenceKeys) && $this->keyResolver === null) {
                continue;
            }

            $augmented = $item->augmented();

            if ($this->keyResolver) {
                $keys = call_user_func($this->keyResolver, $augmented);

                if ($keys === null) {
                    $keys = $augmented->keys();
                }

                $referenceKeys[$reference] = $keys;
            } else {
                $referenceKeys[$reference] = $augmented->keys();
            }

            $referenceFields[$reference] = $augmented->blueprintFields();
        }

        for ($i = 0; $i < $count; $i++) {
            $item = $items[$i];
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

        for ($i = 0; $i < count($tree); $i++) {
            $item = $tree[$i];

            $items[] = $item['page'];
            $this->originalValues[$i] = $item;
        }

        return $this->augment($items);
    }

    public function map(callable $callable)
    {
        $items = [];

        for ($i = 0; $i < count($this->originalValues); $i++) {
            $original = $this->originalValues[$i];
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
