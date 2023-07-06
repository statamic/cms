<?php

namespace Statamic\Search\Null;

use Statamic\Search\QueryBuilder;

class NullQuery extends QueryBuilder
{
    public function getSearchResults($query)
    {
        return collect();
    }
}
