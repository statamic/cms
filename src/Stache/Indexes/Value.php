<?php

namespace Statamic\Stache\Indexes;

use Statamic\Query\ResolveValue;

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
        return (new ResolveValue)($item, $this->name);
    }
}
