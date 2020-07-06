<?php

namespace Statamic\Events\Data;

class AssetDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Asset deleted');
    }
}
