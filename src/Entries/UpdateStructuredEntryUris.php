<?php

namespace Statamic\Entries;

use Statamic\Events\CollectionTreeSaved;

class UpdateStructuredEntryUris
{
    public function handle(CollectionTreeSaved $event)
    {
        $tree = $event->tree;
        $collection = $tree->collection();

        // If it's orderable (single depth structure) then changing the
        // position of the entries is never going to affect the uris.
        if ($collection->orderable()) {
            return;
        }

        // If the collection has no route, there are no uris to update.
        if (! $collection->route($tree->locale())) {
            return;
        }

        $diff = $tree->diff();

        $ids = array_merge($diff->ancestryChanged(), $diff->added());

        if (empty($ids)) {
            return;
        }

        $collection->updateEntryUris($ids);
    }
}
