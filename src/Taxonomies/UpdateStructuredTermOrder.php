<?php

namespace Statamic\Taxonomies;

use Statamic\Events\TaxonomyTreeSaved;
use Statamic\Structures\TaxonomyTree;

class UpdateStructuredTermOrder
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

        $taxonomy->updateTermOrder(
            collect($this->slugsWithDescendants($tree, $moved, $slugs))
                ->map(fn ($slug) => $taxonomy->handle().'::'.$slug)
                ->all()
        );
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
