<?php

namespace Statamic\Entries;

use Statamic\Events\CollectionTreeSaved;
use Statamic\Structures\CollectionTree;

class UpdateStructuredEntryOrderAndParent
{
    public function handle(CollectionTreeSaved $event)
    {
        $tree = $event->tree;
        $collection = $tree->collection();
        $diff = $tree->diff();

        $moved = $diff->moved();
        $ids = array_merge($moved, $diff->added());

        if (empty($ids)) {
            return;
        }

        $collection->updateEntryParent($ids);
        $collection->updateEntryOrder($this->idsWithDescendants($tree, $moved, $ids));
    }

    private function idsWithDescendants(CollectionTree $tree, array $moved, array $ids): array
    {
        return collect($moved)
            ->flatMap(function ($id) use ($tree) {
                if (! $page = $tree->find($id)) {
                    return [];
                }

                return $page->flattenedPages()->map->reference()->all();
            })
            ->merge($ids)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
