<?php

namespace Statamic\Stache\Indexes;

class Uri extends Index
{
    public function getItems()
    {
        return $this->store->getItemsFromFiles()->map->uri()->all();
    }
}