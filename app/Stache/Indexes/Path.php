<?php

namespace Statamic\Stache\Indexes;

class Path extends Index
{
    public function getItems()
    {
        return $this->store->getItemsFromFiles()->map->path()->all();
    }
}