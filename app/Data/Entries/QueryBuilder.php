<?php

namespace Statamic\Data\Entries;

use Statamic\API\Entry;
use Statamic\API\Stache;
use Statamic\Data\QueryBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder
{
    protected $collections;
    protected $site;
    protected $taxonomyTerm;

    public function where($column, $operator = null, $value = null)
    {
        if ($column === 'collection') {
            $this->collections[] = $operator;
            return $this;
        }

        if ($column === 'site') {
            $this->site = $operator;
            return $this;
        }

        return parent::where($column, $operator, $value);
    }

    public function whereIn($column, $values)
    {
        if (in_array($column, ['collection', 'collections'])) {
            $this->collections = array_merge($this->collections ?? [], $values);
            return $this;
        }

        return parent::whereIn($column, $values);
    }

    protected function getBaseItems()
    {
        if ($this->taxonomyTerm) {
            $entries = $this->getBaseTaxonomizedEntries();
        } elseif ($this->collections) {
            $entries = $this->getBaseCollectionEntries();
        } else {
            $entries = Entry::all()->values();
        }

        if ($this->site) {
            $entries = $entries->localize($this->site);
        }

        return $entries;
    }

    protected function getBaseCollectionEntries()
    {
        return collect_entries($this->collections)->flatMap(function ($collection) {
            return Entry::whereCollection($collection);
        })->values();
    }

    protected function getBaseTaxonomizedEntries()
    {
        $associations = Stache::store('terms')->getAssociations();

        [$taxonomy, $slug] = explode('::', $this->taxonomyTerm);

        $ids = collect($associations[$taxonomy][$slug] ?? [])->pluck('id')->all();

        $query = Entry::query()->whereIn('id', $ids);

        if ($this->collections) {
            $query->whereIn('collection', $this->collections);
        }

        return $query->get();
    }

    protected function collect($items = [])
    {
        return collect_entries($items);
    }

    public function whereTaxonomy($term)
    {
        $this->taxonomyTerm = $term;

        return $this;
    }
}
