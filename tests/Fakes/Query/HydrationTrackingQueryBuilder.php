<?php

namespace Tests\Fakes\Query;

use Generator;
use Mockery;
use Statamic\Search\Index;
use Statamic\Search\PlainResult;
use Statamic\Search\QueryBuilder;

class HydrationTrackingQueryBuilder extends QueryBuilder
{
    protected $results;
    protected $hydrationCounter;

    public function __construct($results, &$counter)
    {
        $this->results = $results;
        $this->hydrationCounter = &$counter;
        parent::__construct(Mockery::mock(Index::class));
    }

    public function getSearchResults($query)
    {
        return $this->results;
    }

    public function getBaseItems()
    {
        return $this->collect($this->results)->map(function ($item) {
            $this->hydrationCounter++;
            $result = new PlainResult($item);
            $result->setScore($item['search_score'] ?? null);

            return $result;
        });
    }

    protected function getBaseItemsLazy(): Generator
    {
        foreach ($this->results as $item) {
            $this->hydrationCounter++;
            $result = new PlainResult($item);
            $result->setScore($item['search_score'] ?? null);
            yield $result;
        }
    }
}
