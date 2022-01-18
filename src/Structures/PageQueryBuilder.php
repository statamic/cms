<?php

namespace Statamic\Structures;

use Illuminate\Support\Collection;
use Statamic\Query\IteratorBuilder;

class PageQueryBuilder extends IteratorBuilder
{
    protected $pages;

    public function withPages(Collection $pages)
    {
        $this->pages = $pages;

        return $this;
    }

    protected function getBaseItems()
    {
        return $this->pages;
    }
}
