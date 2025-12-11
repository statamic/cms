<?php

namespace Statamic\Query;

use Generator;
use Illuminate\Support\Collection;

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

    protected function getBaseItemsLazy(): Generator
    {
        foreach ($this->items as $item) {
            yield $item;
        }
    }

    public function whereStatus($status)
    {
        return $this->where('status', $status);
    }
}
