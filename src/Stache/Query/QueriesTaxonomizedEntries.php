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

    protected function addTaxonomyWheres()
    {
        if (empty($this->taxonomyWheres)) {
            return;
        }

        $entryIds = collect($this->taxonomyWheres)->reduce(function ($ids, $where) {
            $method = 'getKeysForTaxonomyWhere'.$where['type'];
            $keys = $this->$method($where);

            return $ids ? $ids->intersect($keys)->values() : $keys;
        });

        $this->whereIn('id', $entryIds->all());
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
