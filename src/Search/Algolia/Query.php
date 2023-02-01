<?php

namespace Statamic\Search\Algolia;

use Statamic\Search\QueryBuilder;

class Query extends QueryBuilder
{
    public function getSearchResults($query)
    {
        $results = $this->index->searchUsingApi($query);

        return $results->map(function ($result, $i) use ($results) {
            $result['search_score'] = $results->count() - $i;

            return $result;
        });
    }
}
