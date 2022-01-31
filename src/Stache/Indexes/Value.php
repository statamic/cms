<?php

namespace Statamic\Stache\Indexes;

use Statamic\Query\ResolveValue;
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

        return (new ResolveValue)($item, $this->name);
    }
}
