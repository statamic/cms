<?php

namespace Statamic\Tags\Collection;

use Closure;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Tags\Query;

class Entries
{
    use Query\HasConditions,
        Query\HasScopes,
        Query\HasOrderBys,
        Query\GetsResults;

    protected $ignoredParams = ['as'];
    protected $parameters;
    protected $collections;
    protected $site;
    protected $showPublished;
    protected $showUnpublished;
    protected $since;
    protected $until;

    public function __construct($parameters)
    {
        $this->parseParameters($parameters);
    }

    public function get()
    {
        try {
            $query = $this->query();
        } catch (NoResultsExpected $exception) {
            return new EntryCollection;
        }

        return $this->results($query);
    }

    public function count()
    {
        try {
            return $this->query()->count();
        } catch (NoResultsExpected $exception) {
            return 0;
        }
    }

    public function next($currentEntry)
    {
        throw_if(Arr::has($this->parameters, 'paginate'), new \Exception('collection:next is not compatible with [paginate] parameter'));
        throw_if(Arr::has($this->parameters, 'offset'), new \Exception('collection:next is not compatible with [offset] parameter'));

        // TODO: but only if all collections have the same configuration.
        $collection = $this->collections[0];

        if ($this->orderBys->first()->direction === 'desc') {
            $this->orderBys = $this->orderBys->map->reverse();
            $reversed = true;
        }

        if ($collection->orderable()) {
            $query = $this->query()->where('order', '>', $currentEntry->order());
        } elseif ($collection->dated()) {
            $query = $this->query()->where('date', '>', $currentEntry->date());
        } else {
            throw new \Exception('collection:next requires ordered or dated collection');
        }

        return $reversed ?? false
            ? $this->results($query)->reverse()->values()
            : $this->results($query);
    }

    public function previous($currentEntry)
    {
        throw_if(Arr::has($this->parameters, 'paginate'), new \Exception('collection:previous is not compatible with [paginate] parameter'));
        throw_if(Arr::has($this->parameters, 'offset'), new \Exception('collection:previous is not compatible with [offset] parameter'));

        // TODO: but only if all collections have the same configuration.
        $collection = $this->collections[0];

        if ($this->orderBys->first()->direction === 'asc') {
            $this->orderBys = $this->orderBys->map->reverse();
            $reversed = true;
        }

        if ($collection->orderable()) {
            $query = $this->query()->where('order', '<', $currentEntry->order());
        } elseif ($collection->dated()) {
            $query = $this->query()->where('date', '<', $currentEntry->date());
        } else {
            throw new \Exception('collection:previous requires ordered or dated collection');
        }

        return $reversed ?? false
            ? $this->results($query)->reverse()->values()
            : $this->results($query);
    }

    protected function query()
    {
        $query = Entry::query()
            ->whereIn('collection', $this->collections->map->handle()->all());

        $this->querySite($query);
        $this->queryPublished($query);
        $this->queryPastFuture($query);
        $this->querySinceUntil($query);
        $this->queryTaxonomies($query);
        $this->queryConditions($query);
        $this->queryScopes($query);
        $this->queryOrderBys($query);

        return $query;
    }

    protected function parseParameters($params)
    {
        $this->parameters = Arr::except($params->all(), $this->ignoredParams);
        $this->collections = $this->parseCollections();
        $this->orderBys = $this->parseOrderBys();
        $this->site = Arr::getFirst($this->parameters, ['site', 'locale']);
        $this->showPublished = Arr::get($this->parameters, 'show_published', true);
        $this->showUnpublished = Arr::get($this->parameters, 'show_unpublished', false);
        $this->since = Arr::get($this->parameters, 'since');
        $this->until = Arr::get($this->parameters, 'until');
    }

    protected function parseCollections()
    {
        $from = Arr::getFirst($this->parameters, ['from', 'in', 'folder', 'use', 'collection']);
        $not = Arr::getFirst($this->parameters, ['not_from', 'not_in', 'not_folder', 'dont_use', 'not_collection']);

        if ($from === '*') {
            $from = Collection::handles();
        } elseif (is_string($from)) {
            $from = explode('|', $from);
        }

        if (is_string($not)) {
            $not = explode('|', $not);
        }

        return collect($from)
            ->diff(collect($not)->filter())
            ->map(function ($handle) {
                $collection = Collection::findByHandle($handle);
                throw_unless($collection, new \Statamic\Exceptions\CollectionNotFoundException($handle));
                return $collection;
            })
            ->values();
    }

    protected function defaultOrderBy()
    {
        // TODO: but only if all collections have the same configuration.
        $collection = $this->collections[0];

        if ($collection->orderable()) {
            return 'order:asc';
        } elseif ($collection->dated()) {
            return 'date:desc|title:asc';
        }

        return 'title:asc';
    }

    protected function querySite($query)
    {
        $site = Arr::getFirst($this->parameters, ['site', 'locale'], Site::current()->handle());

        if ($site === '*' || ! Site::hasMultiple()) {
            return;
        }

        return $query->where('site', $site);
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

    protected function queryTaxonomies($query)
    {
        collect($this->parameters)->filter(function ($value, $key) {
            return $key === 'taxonomy' || Str::startsWith($key, 'taxonomy:');
        })->each(function ($values, $param) use ($query) {
            $taxonomy = substr($param, 9);
            [$taxonomy, $modifier] = array_pad(explode(':', $taxonomy), 2, 'any');

            if (is_string($values)) {
                $values = explode('|', $values);
            }

            $values = collect($values)->map(function ($term) use ($taxonomy) {
                return Str::contains($term, '::') ? $term : $taxonomy . '::' . $term;
            });

            if ($modifier === 'all') {
                $values->each(function ($value) use ($query) {
                    $query->whereTaxonomy($value);
                });

            } elseif ($modifier === 'any') {
                $query->whereTaxonomyIn($values->all());

            } else {
                throw new InvalidArgumentException(
                    'Unknown taxonomy query modifier ['.$modifier.']. Valid values are "any" and "all".'
                );
            }
        });
    }
}
