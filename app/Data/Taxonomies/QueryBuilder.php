<?php

namespace Statamic\Data\Taxonomies;

use Statamic\API\Term;
use Statamic\Data\QueryBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder
{
    protected $taxonomies;
    protected $site;

    public function where($column, $operator = null, $value = null)
    {
        if ($column === 'taxonomy') {
            $this->taxonomies[] = $operator;
            return $this;
        }

        if ($column === 'site') {
            throw new \Exception('handle querying terms by site');
            $this->site = $operator;
            return $this;
        }

        return parent::where($column, $operator, $value);
    }

    public function whereIn($column, $values)
    {
        if (in_array($column, ['taxonomy', 'taxonomies'])) {
            $this->taxonomies = array_merge($this->taxonomies ?? [], $values);
            return $this;
        }

        return parent::whereIn($column, $values);
    }

    protected function getBaseItems()
    {
        $terms = $this->taxonomies
            ? collect_terms($this->taxonomies)->flatMap(function ($taxonomy) {
                return Term::whereTaxonomy($taxonomy);
            })->values()
            : Term::all()->values();

        if ($this->site) {
            $terms = $terms->localize($this->site);
        }

        return $terms;
    }

    protected function collect($items = [])
    {
        return collect_terms($items);
    }
}
