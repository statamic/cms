<?php

namespace Statamic\Query;

use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Stache\Query\QueriesTaxonomizedEntries;

class EmptyEntryQueryBuilder extends EmptyQueryBuilder implements QueryBuilder
{
    use QueriesTaxonomizedEntries;

    public function whereStatus($status)
    {
        return $this;
    }
}
