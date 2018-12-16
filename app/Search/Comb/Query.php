<?php

namespace Statamic\Search\Comb;

use Statamic\API\Content;
use Statamic\Data\QueryBuilder;

class Query extends QueryBuilder
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
        $results = $this->index->lookup($this->query);

        if (! $this->withData) {
            return new \Statamic\Data\DataCollection($results);
        }

        return collect_entries($results)->map(function ($result) {
            return Content::find($result['id']);
        })->filter();
    }
}
