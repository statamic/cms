<?php

namespace Statamic\Search\Comb;

use Statamic\Search\QueryBuilder;

class Query extends QueryBuilder
{
    protected $lookups = [];

    public function getSearchResults($query)
    {
        return $this->lookups[$this->query] ??= $this->index->lookup($this->query);
    }
}
