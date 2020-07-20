<?php

namespace Statamic\Events;

class CollectionDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Collection deleted');
    }
}
