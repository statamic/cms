<?php

namespace Statamic\Tags\Collection;

use Closure;
use Statamic\API;
use Statamic\API\Arr;
use Statamic\API\Entry;
use Statamic\API\Collection;
use Illuminate\Support\Carbon;

class Entries
{
    use HasConditions;

    protected $collections;
    protected $ignoredParams = ['as'];
    protected $parameters;
    protected $site;
    protected $limit;
    protected $offset;
    protected $paginate;
    protected $showPublished;
    protected $showUnpublished;
    protected $showPast;
    protected $since;
    protected $until;
    protected $scopes;
    protected $sort;

    public function __construct($parameters)
    {
        $this->parameters = $this->parseParameters($parameters);
    }

    public function get()
    {
        try {
            $query = $this->query();
        } catch (NoResultsExpected $exception) {
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

    public function count()
    {
        try {
            return $this->query()->count();
        } catch (NoResultsExpected $exception) {
            return 0;
        }
    }

    protected function query()
    {
        $query = Entry::query()
            ->whereIn('collection', $this->collections->map->handle()->all());

        $this->querySite($query);
        $this->queryPublished($query);
        $this->queryPastFuture($query);
        $this->querySinceUntil($query);
        $this->queryConditions($query);
        $this->queryScopes($query);
        $this->querySort($query);

        return $query;
    }

    protected function parseParameters($params)
    {
        $params = Arr::except($params, $this->ignoredParams);

        $this->collections = $this->parseCollections($params);
        $this->site = Arr::getFirst($params, ['site', 'locale']);

        $this->limit = Arr::get($params, 'limit');
        $this->offset = Arr::get($params, 'offset');
        $this->paginate = Arr::get($params, 'paginate');

        if ($this->paginate === true) {
            $this->paginate = $this->limit;
        }

        $this->showPublished = Arr::get($params, 'show_published', true);
        $this->showUnpublished = Arr::get($params, 'show_unpublished', false);
        $this->since = Arr::get($params, 'since');
        $this->until = Arr::get($params, 'until');

        $this->scopes = $this->parseQueryScopes($params);

        $this->sort = Arr::get($params, 'sort');

        return $params;
    }

    protected function parseCollections($params)
    {
        $from = Arr::getFirst($params, ['from', 'in', 'folder', 'use', 'collection']);
        $not = Arr::getFirst($params, ['not_from', 'not_in', 'not_folder', 'dont_use', 'not_collection']);

        $collections = $from === '*'
            ? collect(Collection::handles())
            : collect(explode('|', $from));

        $excludedCollections = collect(explode('|', $not))->filter();

        return $collections
            ->diff($excludedCollections)
            ->map(function ($handle) {
                $collection = Collection::whereHandle($handle);
                throw_unless($collection, new \Exception("Collection [{$handle}] does not exist."));
                return $collection;
            });
    }

    protected function parseQueryScopes($params)
    {
        $scopes = Arr::getFirst($params, ['query', 'filter']);

        return collect(explode('|', $scopes));
    }

    protected function querySite($query)
    {
        if (! $this->site) {
            return;
        }

        return $query->where('site', $this->site);
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
        if (! $this->allCollectionsAreDates()) {
            return;
        }

        // Collection date behaviors
        // TODO: but only if all collections have the same configuration.
        $collection = $this->collections[0];
        $showFuture = $collection->futureDateBehavior() === 'public';
        $showPast = $collection->pastDateBehavior() === 'public';

        // Override by tag parameters.
        $showFuture = $this->parameters['show_future'] ?? $showFuture;
        $showPast = $this->parameters['show_past'] ?? $showPast;

        if ($showFuture && $showPast) {
            return;
        } elseif ($showFuture && ! $showPast) {
            return $query->where('date', '>', Carbon::now());
        } elseif (! $showFuture && $showPast) {
            return $query->where('date', '<', Carbon::now());
        }

        throw new NoResultsExpected;
    }

    protected function querySinceUntil($query)
    {
        if (! $this->allCollectionsAreDates()) {
            return;
        }

        if ($this->since) {
            $query->where('date', '>', Carbon::parse($this->since));
        }

        if ($this->until) {
            $query->where('date', '<', Carbon::parse($this->until));
        }
    }

    public function queryScopes($query)
    {
        $this->scopes
            ->map(function ($handle) {
                return app('statamic.scopes')->get($handle);
            })
            ->filter()
            ->each(function ($class) use ($query) {
                $scope = app($class);
                $scope->apply($query, $this->parameters);
            });
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

    protected function allCollectionsAreDates()
    {
        return $this->allCollectionsAre(function ($collection) {
            return $collection->dated();
        });
    }

    protected function allCollectionsAre(Closure $condition)
    {
        return $this->collections->reject(function ($collection) use ($condition) {
            return $condition($collection);
        })->isEmpty();
    }
}
