<?php

namespace Statamic\Query;

use Illuminate\Support\Collection;
use Statamic\Query\IteratorBuilder;

class ItemQueryBuilder extends IteratorBuilder
{
    protected $items;

    public function withItems(Collection $items)
    {
        $this->items = $items;

        return $this;
    }

    protected function getBaseItems()
    {
        return $this->items;
    }
}
