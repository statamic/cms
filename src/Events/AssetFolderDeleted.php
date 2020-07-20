<?php

namespace Statamic\Events;

class AssetFolderDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Asset folder deleted');
    }
}
