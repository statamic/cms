<?php

namespace Statamic\Stache\Query;

use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;

trait QueriesTaxonomizedEntries
{
    protected $taxonomyWheres = [];

    protected $expandTaxonomyDescendants = true;

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

    /**
     * Terms in taxonomy wheres will also match entries tagged with any of
     * their descendant terms (on hierarchical taxonomies).
     */
    public function withTaxonomyDescendants($expand = true)
    {
        $this->expandTaxonomyDescendants = $expand;

        return $this;
    }

    protected function addTaxonomyWheres()
    {
        if (empty($this->taxonomyWheres)) {
            return;
        }

        $wheres = $this->expandTaxonomyDescendants
            ? $this->expandTaxonomyWheres($this->taxonomyWheres)
            : $this->taxonomyWheres;

        $entryIds = collect($wheres)
            ->reject(function ($where) {
                return $where['type'] === 'NotIn';
            })
            ->reduce(function ($ids, $where) {
                $method = 'getKeysForTaxonomyWhere'.$where['type'];
                $keys = $this->$method($where);

                return $ids ? $ids->intersect($keys)->values() : $keys;
            });

        $excludedEntryIds = collect($wheres)
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

    private function expandTaxonomyWheres($wheres)
    {
        return collect($wheres)->map(function ($where) {
            $values = $where['type'] === 'Basic' ? [$where['value']] : $where['values'];

            $values = collect($values)
                ->flatMap(fn ($id) => $this->taxonomyTermWithDescendants($id))
                ->unique()
                ->values()
                ->all();

            return [
                'type' => $where['type'] === 'NotIn' ? 'NotIn' : 'In',
                'values' => $values,
            ];
        })->all();
    }

    private function taxonomyTermWithDescendants($id)
    {
        [$handle, $slug] = explode('::', $id);

        $taxonomy = Taxonomy::findByHandle($handle);

        if (! $taxonomy || ! $taxonomy->hierarchical() || ! ($page = $taxonomy->structure()->tree()->find($slug))) {
            return [$id];
        }

        return $page->flattenedPages()
            ->map(fn ($descendant) => $handle.'::'.$descendant->id())
            ->prepend($id)
            ->all();
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
