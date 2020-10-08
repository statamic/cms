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
            return $item->queryEntries()->count();
        }

        if (method_exists($item, $method)) {
            return $item->{$method}();
        }

        $field = $item->blueprint()->field($this->name);

        if ($field && $field->fieldtype()->handle() === 'date') {
            return $item->augmentedValue($this->name)->value();
        }

        return $item->value($this->name);
    }
}
