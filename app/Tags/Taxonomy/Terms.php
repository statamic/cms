<?php

namespace Statamic\Tags\Taxonomy;

use Closure;
use Statamic\API;
use Statamic\API\Arr;
use Statamic\API\Site;
use Statamic\API\Term;
use Statamic\Tags\Query;
use Statamic\API\Taxonomy;
use Statamic\API\Collection;
use Illuminate\Support\Carbon;

class Terms
{
    use Query\HasConditions,
        Query\HasScopes,
        Query\HasOrderBys,
        Query\GetsResults;

    protected $ignoredParams = ['as'];
    protected $parameters;
    protected $taxonomies;

    public function __construct($parameters)
    {
        $this->parseParameters($parameters);
    }

    public function get()
    {
        try {
            $query = $this->query();
        } catch (NoResultsExpected $exception) {
            return collect_terms();
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

    protected function query()
    {
        $query = Term::query()
            ->whereIn('taxonomy', $this->taxonomies->map->handle()->all());

        $this->queryConditions($query);
        $this->queryScopes($query);
        $this->queryOrderBys($query);

        return $query;
    }

    protected function parseParameters($params)
    {
        $this->parameters = Arr::except($params->all(), $this->ignoredParams);
        $this->taxonomies = $this->parseTaxonomies();
        $this->orderBys = $this->parseOrderBys();
    }

    protected function parseTaxonomies()
    {
        $from = Arr::getFirst($this->parameters, ['from', 'in', 'folder', 'use', 'taxonomy']);
        $not = Arr::getFirst($this->parameters, ['not_from', 'not_in', 'not_folder', 'dont_use', 'not_taxonomy']);

        $taxonomies = $from === '*'
            ? collect(Taxonomy::handles())
            : collect(explode('|', $from));

        $excludedTaxonomies = collect(explode('|', $not))->filter();

        return $taxonomies
            ->diff($excludedTaxonomies)
            ->map(function ($handle) {
                $taxonomy = Taxonomy::findByHandle($handle);
                throw_unless($taxonomy, new \Exception("Taxonomy [{$handle}] does not exist."));
                return $taxonomy;
            })
            ->values();
    }

    protected function defaultOrderBy()
    {
        return 'title:asc';
    }
}
