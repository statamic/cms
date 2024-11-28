<?php

namespace Statamic\Query;

class EmptyQueryBuilder extends IteratorBuilder
{
    protected function getBaseItems()
    {
        return collect([]);
    }

    public function whereStatus($status)
    {
        return $this;
    }
}
