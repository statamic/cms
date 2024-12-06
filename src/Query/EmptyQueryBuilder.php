<?php

namespace Statamic\Query;

class EmptyQueryBuilder extends IteratorBuilder
{
    protected function getBaseItems()
    {
        return collect([]);
    }
}
