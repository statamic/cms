<?php

namespace Statamic\Stache\Indexes;

class Value extends Index
{
    public function getItems()
    {
        return $this->store->getItemsFromFiles()
            ->map->value($this->name)
            ->all();
    }
}