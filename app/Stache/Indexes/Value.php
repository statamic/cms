<?php

namespace Statamic\Stache\Indexes;

class Value extends Index
{
    public function getItems()
    {
        return $this->store->getItemsFromFiles()->map(function ($item) {
            return $this->getItemValue($item);
        })->all();
    }

    public function getItemValue($item)
    {
        return method_exists($item, $this->name)
            ? $item->{$this->name}()
            : $item->value($this->name);
    }
}