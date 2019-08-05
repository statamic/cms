<?php

namespace Statamic\Data\Taxonomies;

use Statamic\API\Term;
use Statamic\API\Taxonomy;
use Statamic\Data\QueryBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder
{
    protected $taxonomies;
    protected $site;
    protected $collections;

    public function where($column, $operator = null, $value = null)
    {
        if ($column === 'taxonomy') {
            $this->taxonomies[] = $operator;
            return $this;
        }

        if ($column === 'collection') {
            $this->collections[] = $operator;
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

        if (in_array($column, ['collection', 'collections'])) {
            $this->collections = array_merge($this->collections ?? [], $values);
            return $this;
        }

        return parent::whereIn($column, $values);
    }

    protected function getBaseItems()
    {
        if ($this->collections) {
            $terms = $this->getTermsFromCollections();
        } elseif ($this->taxonomies) {
            $terms = Term::whereInTaxonomy($this->taxonomies);
        } else {
            $terms = Term::all();
        }

        if ($this->site) {
            $terms = $terms->localize($this->site);
        }

        return $terms->values();
    }

    protected function getTermsFromCollections()
    {
        $stache = app('stache')->store('terms');
        $taxonomies = $this->taxonomies ?? Taxonomy::all();

        return $this->collect($taxonomies)->flatMap(function ($taxonomy) use ($stache) {
            $termIds = $stache->getCollectionTermIds($taxonomy, $this->collections);
            return $this->collect($termIds)->map(function ($id) {
                return Term::find($id);
            });
        });
    }

    protected function collect($items = [])
    {
        return collect_terms($items);
    }
}
