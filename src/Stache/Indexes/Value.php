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
        $nameExploded = explode('->', $this->name);
        while (! empty($nameExploded)) {
            $name = array_shift($nameExploded);
            $item = $this->getItemPartValue($item, $name);
            if (is_null($item)) {
                return;
            }
        }

        return $item;
    }

    // any changes to this method should also be reflected in Statamic\Query\IteratorBuilder::getFilterItemPartValue()
    private function getItemPartValue($item, $name)
    {
        $method = Str::camel($name);

        if ($method === 'blueprint') {
            return $item->blueprint()->handle();
        }

        if ($method === 'entriesCount') {
            return $item->entriesCount();
        }

        // Don't want to use the authors() method, which would happen right after this.
        if ($method === 'authors') {
            return $item->value('authors');
        }

        if (is_array($item)) {
            return $item[$name] ?? null;
        }

        if (is_scalar($item)) {
            return null;
        }

        if ($item instanceof stdClass) {
            return $item->$name ?? null;
        }

        if (method_exists($item, $method)) {
            return $item->{$method}();
        }

        if (method_exists($item, 'value')) {
            return $item->value($name);
        }

        return $item->get($name);
    }
}
