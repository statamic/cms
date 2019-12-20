<?php

namespace Statamic\Stache\Indexes;

use Statamic\Support\Str;

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
        $method = Str::camel($this->name);

        return method_exists($item, $method)
            ? $item->{$method}()
            : $item->value($this->name);
    }
}
