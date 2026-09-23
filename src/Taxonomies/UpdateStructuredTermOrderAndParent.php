<?php

namespace Statamic\Taxonomies;

use Statamic\Events\TaxonomyTreeSaved;
use Statamic\Structures\TaxonomyTree;

class UpdateStructuredTermOrderAndParent
{
    public function handle(TaxonomyTreeSaved $event)
    {
        $tree = $event->tree;
        $taxonomy = $tree->taxonomy();
        $diff = $tree->diff();

        $moved = $diff->moved();
        $slugs = array_merge($moved, $diff->added());

        if (empty($slugs)) {
            return;
        }

        $taxonomy->updateTermParent($this->ids($taxonomy, $slugs));
        $taxonomy->updateTermOrder($this->ids($taxonomy, $this->slugsWithDescendants($tree, $moved, $slugs)));
    }

    private function ids(Taxonomy $taxonomy, array $slugs): array
    {
        return collect($slugs)->map(fn ($slug) => $taxonomy->handle().'::'.$slug)->all();
    }

    private function slugsWithDescendants(TaxonomyTree $tree, array $moved, array $slugs): array
    {
        return collect($moved)
            ->flatMap(function ($slug) use ($tree) {
                if (! $page = $tree->find($slug)) {
                    return [];
                }

                return $page->flattenedPages()->map->id()->all();
            })
            ->merge($slugs)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
