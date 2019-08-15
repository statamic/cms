<?php

namespace Statamic\Stache\Indexes;

class Slug extends Index
{
    public function getItems()
    {
        return $this->store->getItemsFromFiles()->map->slug()->all();
    }
}