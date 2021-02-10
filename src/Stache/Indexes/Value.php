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

        if ($method === 'blueprint') {
            return $item->blueprint()->handle();
        }

        if ($method === 'entriesCount') {
            return $item->entriesCount();
        }

        return method_exists($item, $method)
            ? $item->{$method}()
            : $item->value($this->name);
    }
}
