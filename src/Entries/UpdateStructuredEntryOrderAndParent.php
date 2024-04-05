<?php

namespace Statamic\Entries;

use Statamic\Events\CollectionTreeSaved;

class UpdateStructuredEntryOrderAndParent
{
    public function handle(CollectionTreeSaved $event)
    {
        $tree = $event->tree;
        $collection = $tree->collection();
        $diff = $tree->diff();

        $ids = array_merge($diff->moved(), $diff->added());

        if (empty($ids)) {
            return;
        }

        $collection->updateEntryOrder($ids);
        $collection->updateEntryParent($ids);
    }
}
