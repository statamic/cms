<?php

namespace Statamic\Entries;

use Statamic\Events\EntrySaved;

class UpdateStructuredEntryChildUris
{
    public function handle(EntrySaved $event)
    {
        $entry = $event->entry;
        $collection = $entry->collection();

        // If the slug hasn't changed, nothing needs to happen.
        if ($entry->isClean('slug')) {
            return;
        }

        // If it's orderable (single depth structure), there are no children to update.
        if ($collection->orderable()) {
            return;
        }

        // If the collection has no route, there are no uris to update.
        if (! $collection->route($entry->locale())) {
            return;
        }

        // If there's no page, there are no children to update.
        if (! $page = $entry->page()) {
            return;
        }

        $ids = $page->flattenedPages()->pluck('id');

        if (empty($ids)) {
            return;
        }

        $collection->updateEntryUris($ids);
    }
}
