<?php

namespace Statamic\Events\Data;

class CollectionDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Collection deleted');
    }
}
