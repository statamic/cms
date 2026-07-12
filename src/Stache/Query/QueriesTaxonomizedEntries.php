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

        $entryIds = collect($this->taxonomyWheres)
            ->reject(function ($where) {
                return $where['type'] === 'NotIn';
            })
            ->reduce(function ($ids, $where) {
                $method = 'getKeysForTaxonomyWhere'.$where['type'];
                $keys = $this->$method($where);

                return $ids ? $ids->intersect($keys)->values() : $keys;
            });

        $excludedEntryIds = collect($this->taxonomyWheres)
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

        return Stache::store('terms')->store($taxonomy)
            ->index('associations')
            ->items()->where('slug', $slug)
            ->pluck('entry');
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
            return Stache::store('terms')->store($taxonomy)
                ->index('associations')
                ->items()->whereIn('slug', $terms->all())
                ->pluck('entry');
        });
    }
}
