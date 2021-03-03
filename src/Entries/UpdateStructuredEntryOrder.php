<?php

namespace Statamic\Entries;

use Statamic\Events\CollectionStructureTreeSaved;

class UpdateStructuredEntryOrder
{
    public function handle(CollectionStructureTreeSaved $event)
    {
        $tree = $event->tree;

        // Only orderable (single depth structure) entries will
        // have order attributes, so don't bother otherwise.
        if (! $tree->collection()->orderable()) {
            return;
        }

        $diff = $tree->diff();

        $ids = array_merge($diff->moved(), $diff->added());

        if (empty($ids)) {
            return;
        }

        $event->tree->collection()->updateEntryOrder($ids);
    }
}
