<?php

namespace Statamic\Tags\Taxonomy;

use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Tags\Concerns;
use Statamic\Taxonomies\TermCollection;

class Terms
{
    use Concerns\QueriesConditions,
        Concerns\QueriesScopes,
        Concerns\QueriesOrderBys,
        Concerns\GetsQueryResults;

    protected $ignoredParams = ['as'];
    protected $params;
    protected $taxonomies;
    protected $collections;

    public function __construct($params)
    {
        $this->parseParameters($params);
    }

    public function get()
    {
        try {
            $query = $this->query();
        } catch (NoResultsExpected $exception) {
            return new TermCollection;
        }

        $terms = $this->results($query);

        // If we can infer which collection is being targeted, we'll add it so
        // that the term URLs resolve to their collection equivalents.
        // eg. /blog/tags/tag instead of just /tags/tag
        if ($this->collections->count() === 1) {
            $terms->each->collection($this->collections[0]);
        }

        return $terms;
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
        $query = Term::query()
            ->whereIn('taxonomy', $this->taxonomies->map->handle()->all());

        if ($this->collections) {
            $query->whereIn('collections', $this->collections->map->handle()->all());
        }

        $this->querySite($query);
        $this->queryConditions($query);
        $this->queryScopes($query);
        $this->queryOrderBys($query);
        $this->queryMinimumEntries($query);

        return $query;
    }

    protected function parseParameters($params)
    {
        $this->params = $params->except($this->ignoredParams);
        $this->taxonomies = $this->parseTaxonomies();
        $this->orderBys = $this->parseOrderBys();
        $this->collections = $this->parseCollections();
    }

    protected function parseTaxonomies()
    {
        $from = Arr::getFirst($this->params, ['from', 'in', 'folder', 'use', 'taxonomy']);
        $not = Arr::getFirst($this->params, ['not_from', 'not_in', 'not_folder', 'dont_use', 'not_taxonomy']);

        $taxonomies = $from === '*'
            ? collect(Taxonomy::handles())
            : collect(explode('|', $from));

        $excludedTaxonomies = collect(explode('|', $not ?? ''))->filter();

        return $taxonomies
            ->diff($excludedTaxonomies)
            ->map(function ($handle) {
                $taxonomy = Taxonomy::findByHandle($handle);
                throw_unless($taxonomy, new \Statamic\Exceptions\TaxonomyNotFoundException($handle));

                return $taxonomy;
            })
            ->values();
    }

    protected function parseCollections()
    {
        $collections = Arr::getFirst($this->params, ['collection', 'collections']);

        if (! $collections) {
            return collect();
        }

        return collect(explode('|', $collections))
            ->map(function ($handle) {
                $collection = Collection::findByHandle($handle);
                throw_unless($collection, new \Statamic\Exceptions\CollectionNotFoundException("Collection [{$handle}] does not exist."));

                return $collection;
            })
            ->values();
    }

    protected function defaultOrderBy()
    {
        return 'title:asc';
    }

    protected function querySite($query)
    {
        $site = $this->params->get(['site', 'locale'], Site::current()->handle());

        if ($site === '*' || ! Site::hasMultiple()) {
            return;
        }

        return $query->where('site', $site);
    }

    protected function queryMinimumEntries($query)
    {
        $isQueryingEntriesCount = $this->params->first(function ($v, $k) {
            return Str::startsWith($k, 'entries_count:');
        });

        if ($isQueryingEntriesCount) {
            return;
        }

        if ($count = $this->params->int('min_count')) {
            $query->where('entries_count', '>=', $count);
        }
    }
}
