<?php

namespace Tests\Fakes\Query;

use Generator;
use Statamic\Data\DataCollection;
use Statamic\Query\IteratorBuilder;

class TestIteratorBuilder extends IteratorBuilder
{
    protected $items;
    protected $loadCounter;

    public function __construct($items, &$counter)
    {
        $this->items = $items;
        $this->loadCounter = &$counter;
    }

    protected function getBaseItems()
    {
        $this->items->each(function () {
            $this->loadCounter++;
        });

        return new DataCollection($this->items->all());
    }

    protected function getBaseItemsLazy(): Generator
    {
        foreach ($this->items as $item) {
            $this->loadCounter++;
            yield $item;
        }
    }
}
