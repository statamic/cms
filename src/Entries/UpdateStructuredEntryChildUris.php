<?php

namespace Statamic\Entries;

use Statamic\Events\EntrySaved;

class UpdateStructuredEntryChildUris
{
    public function handle(EntrySaved $event)
    {
        $entry = $event->entry;
        $collection = $entry->collection();

        // If it's orderable (single depth structure) then changing the
        // position of the entries is never going to affect the uris.
        if ($collection->orderable()) {
            return;
        }

        // If the collection has no route, there are no uris to update.
        if (! $collection->route($entry->locale())) {
            return;
        }

        $ids = $entry->page()->flattenedPages()->pluck('id');

        if (empty($ids)) {
            return;
        }

        $collection->updateEntryUris($ids);
    }
}
