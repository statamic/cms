<?php

namespace Statamic\Search;

use Statamic\API\Content;
use Statamic\Data\QueryBuilder as BaseQueryBuilder;

abstract class QueryBuilder extends BaseQueryBuilder
{
    protected $query;
    protected $index;
    protected $withData = true;

    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    public function query($query)
    {
        $this->query = $query;

        return $this;
    }

    public function withData(bool $with)
    {
        $this->withData = $with;

        return $this;
    }

    public function withoutData()
    {
        $this->withData = false;

        return $this;
    }

    public function getBaseItems()
    {
        $results = $this->getSearchResults($this->query);

        if (! $this->withData) {
            return new \Statamic\Data\DataCollection($results);
        }

        return $this->collect($results)->map(function ($result) {
            return Content::find($result['id']);
        })->filter();
    }

    protected function collect($items = [])
    {
        return collect_entries($items);
    }
}
