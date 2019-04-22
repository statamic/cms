<?php

namespace Statamic\Tags\Collection;

use Statamic\API;
use Statamic\API\Arr;
use Illuminate\Support\Carbon;

class Entries
{
    use HasConditions;

    protected $collection;
    protected $parameters;
    protected $limit = false;
    protected $offset = false;
    protected $paginate = false;
    protected $showPublished = true;
    protected $showUnpublished = false;
    protected $showPast = true;
    protected $showFuture = false;
    protected $since;
    protected $until;
    protected $sort;
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
            $this->querySinceUntil($query);
            $this->queryConditions($query);
            $this->querySort($query);
        } catch (NoResultsExpected $e) {
            return collect_entries();
        }

        if ($perPage = $this->paginate) {
            return $query->paginate($perPage);
        }

        if ($limit = $this->limit) {
            $query->limit($limit);
        }

        if ($offset = $this->offset) {
            $query->offset($offset);
        }

        return $query->get();
    }

    protected function parseParameters($params)
    {
        $params = array_except($params, $this->ignoredParams);

        $this->limit = Arr::pull($params, 'limit', $this->limit);
        $this->offset = Arr::pull($params, 'offset', $this->offset);
        $this->paginate = Arr::pull($params, 'paginate', $this->paginate);

        if ($this->paginate === true) {
            $this->paginate = $this->limit;
        }

        $this->showPublished = Arr::pull($params, 'show_published', $this->showPublished);
        $this->showUnpublished = Arr::pull($params, 'show_unpublished', $this->showUnpublished);

        $this->showPast = Arr::pull($params, 'show_past', $this->showPast);
        $this->showFuture = Arr::pull($params, 'show_future', $this->showFuture);

        $this->since = Arr::pull($params, 'since', $this->since);
        $this->until = Arr::pull($params, 'until', $this->until);

        $this->sort = Arr::pull($params, 'sort', $this->sort);

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

    protected function querySinceUntil($query)
    {
        if ($this->collection->order() !== 'date') {
            return;
        }

        if ($this->since) {
            $query->where('date', '>', Carbon::parse($this->since));
        }

        if ($this->until) {
            $query->where('date', '<', Carbon::parse($this->until));
        }
    }

    public function querySort($query)
    {
        if (! $this->sort) {
            return;
        }

        $sort = explode(':', $this->sort)[0];
        $direction = explode(':', $this->sort)[1] ?? 'asc';

        $query->orderBy($sort, $direction);
    }
}
