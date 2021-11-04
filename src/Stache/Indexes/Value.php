<?php

namespace Statamic\Stache\Indexes;

use Statamic\Support\Str;
use Illuminate\Support\Facades\Cache;

class Value extends Index
{
    public function getItems()
    {
        return $this->store->getItemsFromFiles()->map(function ($item) {
            $this->cacheItemTimestamp($item);

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

        // Don't want to use the authors() method, which would happen right after this.
        if ($method === 'authors') {
            return $item->value('authors');
        }

        if (method_exists($item, $method)) {
            return $item->{$method}();
        }

        if (method_exists($item, 'value')) {
            return $item->value($this->name);
        }

        return $item->get($this->name);
    }

    public function cacheItemTimestamp($item)
    {
        $scheduledItems = Cache::get($this->timestampCacheKey()) ?? collect();
        $scheduledItems->put($item->id(), $item->date());

        Cache::forever($this->timestampCacheKey(), collect($scheduledItems));
    }

    public function getItemTimestamp($id)
    {
        return Cache::get($this->timestampCacheKey())->get($id);
    }

    protected function timestampCacheKey()
    {
        return "stache::timestamps::{$this->store->key()}";
    }
}
