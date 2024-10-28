<?php

namespace Statamic\Tags\Collection;

use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\Collection;
use Statamic\Facades\Compare;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Query\OrderBy;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Tags\Concerns;

class Entries
{
    use Concerns\GetsQueryResults,
        Concerns\GetsQuerySelectKeys,
        Concerns\QueriesOrderBys,
        Concerns\QueriesScopes,
        Concerns\QueriesTaxonomyTerms;
    use Concerns\QueriesConditions {
        queryableConditionParams as traitQueryableConditionParams;
    }

    protected $ignoredParams = ['as'];
    protected $params;
    protected $collections;
    protected $site;
    protected $showPublished;
    protected $showUnpublished;
    protected $since;
    protected $until;

    public function __construct($params)
    {
        $this->parseParameters($params);
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
        throw_if($this->params->has('paginate'), new \Exception('collection:next is not compatible with [paginate] parameter'));
        throw_if($this->params->has('offset'), new \Exception('collection:next is not compatible with [offset] parameter'));
        throw_if($this->collections->count() > 1, new \Exception('collection:next is not compatible with multiple collections'));

        if ($this->orderBys->count() === 1) {
            $this->orderBys[] = new OrderBy('title', 'asc');
        }

        $collection = $this->collections->first();
        $primaryOrderBy = $this->orderBys->first();
        $secondaryOrderBy = $this->orderBys->get(1);

        $primaryOperator = $primaryOrderBy->direction === 'desc' ? '<' : '>';
        $secondaryOperator = $secondaryOrderBy->direction === 'desc' ? '<' : '>';

        if ($primaryOrderBy->sort === 'order') {
            throw_if(! $currentOrder = $currentEntry->order(), new \Exception('Current entry does not have an order'));
            $query = $this->query()->where('order', $primaryOperator, $currentOrder);
        } elseif ($collection->dated() && $primaryOrderBy->sort === 'date') {
            $query = $this->queryPreviousNextByDate($currentEntry, $primaryOperator, $secondaryOperator);
        } else {
            throw new \Exception('collection:next requires ordered or dated collection');
        }

        return $this->results($query);
    }

    public function previous($currentEntry)
    {
        throw_if($this->params->has('paginate'), new \Exception('collection:previous is not compatible with [paginate] parameter'));
        throw_if($this->params->has('offset'), new \Exception('collection:previous is not compatible with [offset] parameter'));
        throw_if($this->collections->count() > 1, new \Exception('collection:previous is not compatible with multiple collections'));

        if ($this->orderBys->count() === 1) {
            $this->orderBys[] = new OrderBy('title', 'asc');
        }

        $collection = $this->collections->first();
        $primaryOrderBy = $this->orderBys->first();
        $secondaryOrderBy = $this->orderBys->get(1);

        $primaryOperator = $primaryOrderBy->direction === 'desc' ? '>' : '<';
        $secondaryOperator = $secondaryOrderBy->direction === 'desc' ? '>' : '<';

        if ($primaryOrderBy->sort === 'order') {
            throw_if(! $currentOrder = $currentEntry->order(), new \Exception('Current entry does not have an order'));
            $query = $this->query()->where('order', $primaryOperator, $currentOrder);
        } elseif ($collection->dated() && $primaryOrderBy->sort === 'date') {
            $query = $this->queryPreviousNextByDate($currentEntry, $primaryOperator, $secondaryOperator);
        } else {
            throw new \Exception('collection:previous requires ordered or dated collection');
        }

        $limit = $this->params['limit'] ?? false;
        $count = $query->count();

        if ($limit && $limit < $count) {
            $this->params['offset'] = $count - $limit;
        }

        return $this->results($query);
    }

    public function older($currentEntry)
    {
        $collection = $this->collections->first();
        $primaryOrderBy = $this->orderBys->first();

        throw_unless($collection->dated(), new \Exception('collection:older requires a dated collection'));

        return $primaryOrderBy->direction === 'asc'
            ? $this->previous($currentEntry)
            : $this->next($currentEntry);
    }

    public function newer($currentEntry)
    {
        $collection = $this->collections->first();
        $primaryOrderBy = $this->orderBys->first();

        throw_unless($collection->dated(), new \Exception('collection:newer requires a dated collection'));

        return $primaryOrderBy->direction === 'asc'
            ? $this->next($currentEntry)
            : $this->previous($currentEntry);
    }

    protected function queryPreviousNextByDate($currentEntry, string $primaryOperator, string $secondaryOperator): QueryBuilder
    {
        $primaryOrderBy = $this->orderBys->first();
        $secondaryOrderBy = $this->orderBys->get(1);

        $currentEntryDate = $currentEntry->date();

        // Get the IDs of any items that have the same date as the current entry,
        // but come before/after the current entry sorted by the second column.
        $previousOfSame = $this->query()
            ->where('date', $currentEntryDate)
            ->orderBy($secondaryOrderBy->sort, $secondaryOrderBy->direction)
            ->where($secondaryOrderBy->sort, $secondaryOperator, $currentEntry->value($secondaryOrderBy->sort))
            ->get()
            ->pluck('id')
            ->toArray();

        return $this->query()
            ->where(fn ($query) => $query
                ->where('date', $primaryOperator, $currentEntryDate)
                ->orWhereIn('id', $previousOfSame)
            )
            ->orderBy('date', $primaryOrderBy->direction)
            ->orderBy($secondaryOrderBy->sort, $secondaryOrderBy->direction);
    }

    protected function query()
    {
        $query = Entry::query()
            ->whereIn('collection', $this->collections->map->handle()->all());

        $this->querySelect($query);
        $this->querySite($query);
        $this->queryPublished($query);
        $this->queryPastFuture($query);
        $this->querySinceUntil($query);
        $this->queryTaxonomies($query);
        $this->queryRedirects($query);
        $this->queryConditions($query);
        $this->queryOrderBys($query);
        $this->queryScopes($query);

        return $query;
    }

    protected function parseParameters($params)
    {
        $this->params = $params->except($this->ignoredParams);

        $this->collections = $this->parseCollections();
        $this->orderBys = $this->parseOrderBys();
        $this->site = $this->params->get(['site', 'locale']);
        $this->since = $this->params->get('since');
        $this->until = $this->params->get('until');
    }

    protected function parseCollections()
    {
        $from = $this->params->get(['from', 'in', 'folder', 'use', 'collection']);
        $not = $this->params->get(['not_from', 'not_in', 'not_folder', 'dont_use', 'not_collection']);

        if ($from === '*') {
            $from = Collection::handles()->all();
        } elseif (is_string($from)) {
            $from = explode('|', $from);
        }

        if (is_string($not)) {
            $not = explode('|', $not);
        }

        $from = $from instanceof IlluminateCollection ? $from : collect(Arr::wrap($from));
        $not = $not instanceof IlluminateCollection ? $not : collect(Arr::wrap($not));

        $from = $from->map(function ($collection) {
            return (string) $collection;
        });

        $not = $not->map(function ($collection) {
            return (string) $collection;
        })->filter();

        return $from
            ->diff($not)
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

        return $collection->sortField().':'.$collection->sortDirection();
    }

    protected function querySelect($query)
    {
        if ($keys = $this->getQuerySelectKeys(Entry::make())) {
            $query->select($keys);
        }
    }

    protected function querySite($query)
    {
        $site = $this->params->get(['site', 'locale'], Site::current()->handle());

        if ($site === '*' || ! Site::hasMultiple()) {
            return;
        }

        return $query->where('site', $site);
    }

    protected function queryPublished($query)
    {
        if ($this->isQueryingCondition('status') || $this->isQueryingCondition('published')) {
            return;
        }

        return $query->where('published', true);
    }

    protected function queryPastFuture($query)
    {
        if (! $this->allCollectionsAreDates()) {
            return;
        } elseif ($this->isQueryingCondition('status')) {
            return;
        }

        // Collection date behaviors
        // TODO: but only if all collections have the same configuration.
        $collection = $this->collections[0];
        $showFuture = $collection->futureDateBehavior() === 'public';
        $showPast = $collection->pastDateBehavior() === 'public';

        // Override by tag parameters.
        $showFuture = $this->params['show_future'] ?? $showFuture;
        $showPast = $this->params['show_past'] ?? $showPast;

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
        collect($this->params)->filter(function ($value, $key) {
            return $key === 'taxonomy' || Str::startsWith($key, 'taxonomy:');
        })->each(function ($values, $param) use ($query) {
            $taxonomy = substr($param, 9);
            [$taxonomy, $modifier] = array_pad(explode(':', $taxonomy), 2, null);

            if (Compare::isQueryBuilder($values)) {
                $values = $values->get();
            }

            if (is_string($values)) {
                $values = array_filter(explode('|', $values));
            }

            if (is_null($values) || (is_iterable($values) && count($values) === 0)) {
                return;
            }

            if (! is_iterable($values)) {
                $values = [$values];
            }

            $values = collect($values)->map(function ($term) use ($taxonomy) {
                if ($term instanceof Term) {
                    return $term->id();
                }

                return Str::contains($term, '::') ? $term : $taxonomy.'::'.$term;
            });

            $this->queryTaxonomyTerms($query, $modifier, $values);
        });
    }

    protected function queryRedirects($query)
    {
        $isQueryingRedirect = $this->params->first(function ($v, $k) {
            return Str::startsWith($k, 'redirect:');
        });

        if ($isQueryingRedirect) {
            return;
        }

        if (! $this->params->bool(['redirects', 'links'], false)) {
            $query->whereNull('redirect');
        }
    }

    protected function queryableConditionParams()
    {
        return $this->traitQueryableConditionParams()
            ->reject(fn ($value, $key) => Str::startsWith($key, 'taxonomy:'))
            ->reject(fn ($value, $key) => $value === '');
    }
}
