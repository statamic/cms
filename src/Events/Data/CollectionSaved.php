<?php

namespace Statamic\Events\Data;

class CollectionSaved extends Saved
{
    public function commitMessage()
    {
        return __('Collection saved');
    }
}
