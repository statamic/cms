<?php

namespace Statamic\Search\Comb;

use Statamic\Search\QueryBuilder;

class Query extends QueryBuilder
{
    public function getSearchResults($query)
    {
        return $this->index->lookup($this->query);
    }
}
