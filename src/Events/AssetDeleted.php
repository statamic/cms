<?php

namespace Statamic\Events;

class AssetDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Asset deleted');
    }
}
