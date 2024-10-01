<?php

namespace Statamic\Search\Algolia;

use Statamic\Facades\Blink;
use Statamic\Search\QueryBuilder;

class Query extends QueryBuilder
{
    public function getSearchResults($query)
    {
        $key = "search-algolia-{$this->index->name()}-".md5($query);

        $results = Blink::once($key, function () use ($query) {
            return $this->index->searchUsingApi($query);
        });

        return $results->map(function ($result, $i) use ($results) {
            $result['search_score'] = $results->count() - $i;

            return $result;
        });
    }
}
