<?php

namespace Statamic\Stache\Indexes;

class Site extends Index
{
    public function getItems()
    {
        return $this->store->getItemsFromFiles()->map->locale()->all();
    }
}