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

        if (! empty($ids)) {
            $collection->updateEntryParent($ids);
        }

        // Moved entries and their descendants are kept since stale references in the raw tree can hide their shift.
        if ($orderIds = $this->idsWithDescendants($tree, $moved, array_merge($moved, $diff->orderChanged()))) {
            $collection->updateEntryOrder($orderIds);
        }
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
