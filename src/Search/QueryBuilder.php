<?php

namespace Statamic\Search;

use Statamic\Contracts\Search\Result;
use Statamic\Data\DataCollection;
use Statamic\Query\IteratorBuilder as BaseQueryBuilder;
use Statamic\Search\Searchables\Providers;
use Statamic\Support\Str;

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
            return $this->collect($results)
                ->map(fn ($result) => new PlainResult($result))
                ->each(fn (Result $result, $i) => $result->setIndex($this->index)->setScore($results[$i]['search_score'] ?? null));
        }

        return $this->collect($results)->groupBy(function ($result) {
            return Str::before($result['reference'], '::');
        })->flatMap(function ($results, $prefix) {
            $results = $results->keyBy('reference');
            $ids = $results->map(fn ($result) => Str::after($result['reference'], $prefix.'::'))->values()->all();

            return app(Providers::class)
                ->getByPrefix($prefix)
                ->find($ids)
                ->map->toSearchResult()
                ->each(function (Result $result) use ($results) {
                    return $result
                        ->setIndex($this->index)
                        ->setRawResult($raw = $results[$result->getReference()])
                        ->setScore($raw['search_score'] ?? null);
                });
        })
        ->sortByDesc->getScore()
        ->values();
    }

    protected function collect($items = [])
    {
        return new DataCollection($items);
    }
}
