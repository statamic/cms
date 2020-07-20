<?php

namespace Statamic\Events;

class CollectionSaved extends Saved
{
    public function commitMessage()
    {
        return __('Collection saved');
    }
}
