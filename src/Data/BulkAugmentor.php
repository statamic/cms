<?php

namespace Statamic\Data;

use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\BulkAugmentable;

class BulkAugmentor
{
    protected $isTree = false;
    protected $originalValues = [];
    protected $augmentedValues = [];

    protected function getAugmentationReference($item)
    {
        if ($item instanceof BulkAugmentable && $key = $item->getAugmentationReferenceKey()) {
            return $key;
        }

        return 'Ref::'.get_class($item).spl_object_hash($item);
    }

    /**
     * @param  array<Augmentable>  $items
     * @return $this
     */
    public function augment($items)
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

            if (array_key_exists($reference, $referenceKeys)) {
                continue;
            }

            $augmented = $item->augmented();
            $referenceKeys[$reference] = $augmented->keys();
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

    public function augmentTree($tree)
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

    public function augmented()
    {
        return $this->augmentedValues;
    }
}
