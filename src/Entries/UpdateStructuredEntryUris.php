<?php

namespace Statamic\Entries;

use Statamic\Events\CollectionStructureTreeSaved;

class UpdateStructuredEntryUris
{
    public function handle(CollectionStructureTreeSaved $event)
    {
        $ids = $event->tree->diff()->affected();

        if (empty($ids)) {
            return;
        }

        $event->tree->collection()->updateEntryUris($ids);
    }
}
