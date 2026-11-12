<?php

namespace Statamic\Stache\Query;

use Statamic\Facades\Stache;

trait QueriesTaxonomizedEntries
{
    protected $taxonomyWheres = [];

    public function whereTaxonomy($term)
    {
        $this->taxonomyWheres[] = [
            'type' => 'Basic',
            'value' => $term,
        ];

        return $this;
    }

    public function whereTaxonomyIn($term)
    {
        $this->taxonomyWheres[] = [
            'type' => 'In',
            'values' => $term,
        ];

        return $this;
    }

    public function whereTaxonomyNotIn($term)
    {
        $this->taxonomyWheres[] = [
            'type' => 'NotIn',
            'values' => $term,
        ];

        return $this;
    }

    protected function addTaxonomyWheres()
    {
        if (empty($this->taxonomyWheres)) {
            return;
        }

        $taxonomyWheres = $this->taxonomyWheres;

        // They become regular wheres below, so they only need to be applied once,
        // even when the query is run more than once (e.g. count then get).
        $this->taxonomyWheres = [];

        $entryIds = collect($taxonomyWheres)
            ->reject(function ($where) {
                return $where['type'] === 'NotIn';
            })
            ->reduce(function ($ids, $where) {
                $method = 'getKeysForTaxonomyWhere'.$where['type'];
                $keys = $this->$method($where);

                return $ids ? $ids->intersect($keys)->values() : $keys;
            });

        $excludedEntryIds = collect($taxonomyWheres)
            ->filter(function ($where) {
                return $where['type'] === 'NotIn';
            })
            ->reduce(function ($ids, $where) {
                $keys = $this->getKeysForTaxonomyWhereIn($where);

                return $ids ? $ids->intersect($keys)->values() : $keys;
            });

        if ($entryIds) {
            $this->whereIn('id', $entryIds->all());
        }

        if ($excludedEntryIds) {
            $this->whereNotIn('id', $excludedEntryIds->all());
        }
    }

    private function getKeysForTaxonomyWhereBasic($where)
    {
        $term = $where['value'];

        [$taxonomy, $slug] = explode('::', $term);

        return collect(Stache::store('terms')->store($taxonomy)->index('associations')->forSlug($slug))
            ->pluck('entry')
            ->values();
    }

    private function getKeysForTaxonomyWhereIn($where)
    {
        // Get the terms grouped by taxonomy.
        // [tags::foo, categories::baz, tags::bar]
        // becomes [tags => [foo, bar], categories => [baz]]
        $taxonomies = collect($where['values'])
            ->map(function ($value) {
                [$taxonomy, $term] = explode('::', $value);

                return compact('taxonomy', 'term');
            })
            ->groupBy->taxonomy
            ->map(function ($group) {
                return collect($group)->map->term;
            });

        return $taxonomies->flatMap(function ($terms, $taxonomy) {
            return collect(Stache::store('terms')->store($taxonomy)->index('associations')->forSlugs($terms->all()))
                ->pluck('entry')
                ->values();
        });
    }
}
