<?php

namespace Statamic\Tags\Collection;

use Statamic\API;
use Statamic\API\Arr;
use Illuminate\Support\Carbon;

class Entries
{
    protected $collection;
    protected $parameters;
    protected $paginate = false;
    protected $perPage;
    protected $showPublished = true;
    protected $showUnpublished = false;
    protected $showPast = true;
    protected $showFuture = false;
    protected $ignoredParams = ['from', 'as'];

    public function __construct($collection, $parameters)
    {
        $this->collection = API\Collection::whereHandle($collection);
        $this->parameters = $this->parseParameters($parameters);
    }

    public function get()
    {
        $query = $this->collection->queryEntries();

        try {
            $this->queryPublished($query);
            $this->queryPastFuture($query);
        } catch (NoResultsExpected $e) {
            return collect_entries();
        }

        return $this->paginate ? $query->paginate($this->perPage) : $query->get();
    }

    protected function parseParameters($params)
    {
        $params = array_except($params, $this->ignoredParams);

        if ($this->paginate = Arr::pull($params, 'paginate', $this->paginate)) {
            $this->perPage = Arr::pull($params, 'limit');
        }

        $this->showPublished = Arr::pull($params, 'show_published', $this->showPublished);
        $this->showUnpublished = Arr::pull($params, 'show_unpublished', $this->showUnpublished);

        $this->showPast = Arr::pull($params, 'show_past', $this->showPast);
        $this->showFuture = Arr::pull($params, 'show_future', $this->showFuture);

        return $params;
    }

    protected function queryPublished($query)
    {
        if ($this->showPublished && $this->showUnpublished) {
            return;
        } elseif ($this->showPublished && ! $this->showUnpublished) {
            return $query->where('published', true);
        } elseif (! $this->showPublished && $this->showUnpublished) {
            return $query->where('published', false);
        }

        throw new NoResultsExpected;
    }

    protected function queryPastFuture($query)
    {
        if ($this->collection->order() !== 'date') {
            return;
        }

        if ($this->showFuture && $this->showPast) {
            return;
        } elseif ($this->showFuture && ! $this->showPast) {
            return $query->where('date', '>', Carbon::now());
        } elseif (! $this->showFuture && $this->showPast) {
            return $query->where('date', '<', Carbon::now());
        }

        throw new NoResultsExpected;
    }
}
