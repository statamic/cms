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

        if (method_exists($item, $method)) {
            return $item->{$method}();
        }

        if (method_exists($item, 'value')) {
            return $item->value($this->name);
        }

        return $item->get($this->name);
    }
}
