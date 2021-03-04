<?php

namespace Statamic\Entries;

use Statamic\Events\CollectionStructureTreeSaved;

class UpdateStructuredEntryUris
{
    public function handle(CollectionStructureTreeSaved $event)
    {
        $tree = $event->tree;

        // If it's orderable (single depth structure) then changing the
        // position of the entries is never going to affect the uris.
        if ($tree->collection()->orderable()) {
            return;
        }

        // If the collection has no route, there are no uris to update.
        if (! $tree->collection()->route($tree->locale())) {
            return;
        }

        $diff = $tree->diff();

        $ids = array_merge($diff->ancestryChanged(), $diff->added());

        if (empty($ids)) {
            return;
        }

        $event->tree->collection()->updateEntryUris($ids);
    }
}
